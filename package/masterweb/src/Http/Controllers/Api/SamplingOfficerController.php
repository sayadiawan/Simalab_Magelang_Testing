<?php

namespace Smt\Masterweb\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Smt\Masterweb\Models\User;
use App\Http\Controllers\Controller;
use Smt\Masterweb\Helpers\SampleCollectorAccess;

class SamplingOfficerController extends Controller
{
    /**
     * Daftar petugas pengambil sampel Kesmas (SOLM).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $page = $request->input('page');
        $resultCount = 10;
        $offset = ($page - 1) * $resultCount;

        $privilegeIds = DB::table('ms_privilege')
            ->whereIn('level', SampleCollectorAccess::kesmasLevels())
            ->whereNull('deleted_at')
            ->pluck('id');

        $baseQuery = User::query()
            ->whereIn('level', $privilegeIds)
            ->whereNull('deleted_at');

        $users = (clone $baseQuery)
            ->when($request->term, function ($query) use ($request) {
                $query->where(function ($inner) use ($request) {
                    $inner->where('name', 'like', "%{$request->term}%")
                        ->orWhere('email', 'like', "%{$request->term}%")
                        ->orWhere('username', 'like', "%{$request->term}%");
                });
            })
            ->skip($offset)
            ->take($resultCount)
            ->select(['id AS id', 'name AS text'])
            ->get();

        $count = (clone $baseQuery)
            ->when($request->term, function ($query) use ($request) {
                $query->where(function ($inner) use ($request) {
                    $inner->where('name', 'like', "%{$request->term}%")
                        ->orWhere('email', 'like', "%{$request->term}%")
                        ->orWhere('username', 'like', "%{$request->term}%");
                });
            })
            ->count();

        $endCount = $offset + $resultCount;
        $morePages = $endCount < $count;

        return response()->json([
            'results' => $users,
            'pagination' => [
                'more' => $morePages,
            ],
        ], 200);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * @param string $token
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ]);
    }
}

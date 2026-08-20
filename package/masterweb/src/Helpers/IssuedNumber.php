<?php

namespace Smt\Masterweb\Helpers;

/**
 * Ambil max nomor urut dari kluster padat, abaikan pulau angka junk (loncat jauh).
 */
class IssuedNumber
{
    /**
     * @param  array<int|string>  $numbers
     */
    public static function maxDense(array $numbers, int $maxGap = 100, int $maxDigits = 6): int
    {
        $limit = (10 ** $maxDigits) - 1;
        $clean = [];
        foreach ($numbers as $n) {
            $v = (int) $n;
            if ($v >= 1 && $v <= $limit) {
                $clean[$v] = $v;
            }
        }
        if ($clean === []) {
            return 0;
        }

        $sorted = array_values($clean);
        sort($sorted, SORT_NUMERIC);

        $clusters = [];
        $curMax = (int) $sorted[0];
        $curCount = 1;

        $count = count($sorted);
        for ($i = 1; $i < $count; $i++) {
            $cur = (int) $sorted[$i];
            $prev = (int) $sorted[$i - 1];
            if (($cur - $prev) <= $maxGap) {
                $curMax = $cur;
                $curCount++;
            } else {
                $clusters[] = ['max' => $curMax, 'count' => $curCount];
                $curMax = $cur;
                $curCount = 1;
            }
        }
        $clusters[] = ['max' => $curMax, 'count' => $curCount];

        $qualified = array_values(array_filter($clusters, function ($c) {
            return $c['count'] >= 3;
        }));
        $pool = $qualified !== [] ? $qualified : $clusters;

        usort($pool, function ($a, $b) {
            return $b['max'] <=> $a['max'];
        });

        return (int) $pool[0]['max'];
    }
}

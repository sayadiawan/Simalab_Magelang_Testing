<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CaptchaController extends Controller
{
	/**
	 * Generate a simple image CAPTCHA and store the code in session.
	 */
	public function generate(Request $request)
	{
		$width = 150;
		$height = 48;
		$length = 5;
		$characters = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
		$code = '';
		for ($i = 0; $i < $length; $i++) {
			$code .= $characters[random_int(0, strlen($characters) - 1)];
		}

		// Store in session with short TTL - ensure session is saved
		$request->session()->put('captcha_code', trim($code));
		$request->session()->put('captcha_time', time());
		$request->session()->save(); // Force save session

		$image = imagecreatetruecolor($width, $height);
		$bg = imagecolorallocate($image, 245, 248, 255);
		$fg = imagecolorallocate($image, 29, 78, 216);
		$noise = imagecolorallocate($image, 199, 210, 254);

		imagefilledrectangle($image, 0, 0, $width, $height, $bg);

		// Add noise lines
		for ($i = 0; $i < 6; $i++) {
			imageline(
				$image,
				random_int(0, $width),
				random_int(0, $height),
				random_int(0, $width),
				random_int(0, $height),
				$noise
			);
		}

		// Draw the code (using built-in font for portability)
		$font = 5; // built-in font size
		$textWidth = imagefontwidth($font) * strlen($code);
		$textHeight = imagefontheight($font);
		$x = (int)(($width - $textWidth) / 2);
		$y = (int)(($height - $textHeight) / 2);
		imagestring($image, $font, $x, $y, $code, $fg);

		// Output
		header('Content-Type: image/png');
		header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
		header('Pragma: no-cache');
		imagepng($image);
		imagedestroy($image);
		exit;
	}
}



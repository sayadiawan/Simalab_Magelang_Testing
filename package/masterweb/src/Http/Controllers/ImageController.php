<?php

namespace Smt\Masterweb\Http\Controllers;

use App\Http\Controllers\Controller;

use Endroid\QrCode\QrCode;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;


class ImageController extends Controller
{
	public function _construct(QrCode $qrCode)
	{
		$this->qrCode = $qrCode;
	}

	public function makeQrCode($text, $size = 100, $margin = 0)
	{
		$writer = new PngWriter();

		// Create QR code
		$qrCode = QrCode::create(route('scan.verification', [$text]))
			->setEncoding(new Encoding('UTF-8'))
			->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
			->setSize($size)
			->setMargin($margin)
			->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
			->setForegroundColor(new Color(0, 0, 0))
			->setBackgroundColor(new Color(255, 255, 255));

		// Create generic logo

		// Create generic label
		$result = $writer->write($qrCode);
		header('Content-Type: ' . $result->getMimeType());
		return $result->getString();
	}
}
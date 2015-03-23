<?php

/**
 * @author Jan Emrich <emrich@mehrkanal.com>
 */

namespace mehrkanal;


class TCPDF2DBarcodeJpeg extends \TCPDF2DBarcode {

    /**
     * Send a Jpeg image representation of barcode (requires GD or Imagick library).
     * @param $w (int) Width of a single rectangle element in pixels.
     * @param $h (int) Height of a single rectangle element in pixels.
     * @param $color (array) RGB (0-255) foreground color for bar elements (background is transparent).
     * @public
     */
    public function getBarcodeJPG($w=3, $h=3, $color=[0,0,0,1]) {
        $data = $this->createBarcodeJpg($w, $h, $color);
        // send headers
        header('Content-Type: image/jpeg');
        header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
        header('Pragma: public');
        header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
        echo $data;
    }

    /**
     * Return a Jpeg image representation of barcode (requires Imagick library).
     * @param int $w Width of a single rectangle element in pixels.
     * @param int $h Height of a single rectangle element in pixels.
     * @param [float] $color Array of CMYK Values (0-100)
     * @return bool
     * @public
     */
    public function createBarcodeJpg($w = 3, $h = 3, $color=[0,0,0,1], $filename = 'qr_code.jpg') {
        // calculate image size
        $width = ($this->barcode_array['num_cols'] * $w);
        $height = ($this->barcode_array['num_rows'] * $h);
        if (extension_loaded('imagick')) {
            $foreground_color = escapeshellarg('cmyk('.$color[0].'%,'.$color[1].'%,'.$color[2].'%,'.$color[3].'%)');
        } else {
            return false;
        }
        $coordinates = [];
        // print barcode elements
        $y = 0;
        // for each row
        for ($row = 0; $row < $this->barcode_array['num_rows']; ++$row) {
            $x = 0;
            // for each column
            for ($column = 0; $column < $this->barcode_array['num_cols']; ++$column) {
                if ($this->barcode_array['bcode'][$row][$column] == 1) {
                    // draw a single barcode cell
                    //					$bar->rectangle($x, $y, ($x + $w - 1), ($y + $h - 1));
                    $coordinates[] = $x.','.$y.','.($x + $w - 1).','.($y + $h - 1);
                }
                $x += $w;
            }
            $y += $h;
        }

        $cmd = 'convert -size '.$width.'x'.$height.' xc:white -fill '.$foreground_color;
        foreach ($coordinates as $coordinate) {
            $cmd .= ' -draw \'rectangle '.$coordinate.'\'';
        }
        $cmd .= ' -colorspace cmyk '.escapeshellarg($filename);
        exec($cmd);
        return true;
    }
}
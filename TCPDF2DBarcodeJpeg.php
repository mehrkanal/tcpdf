<?php

/**
 * @author Jan Emrich <emrich@mehrkanal.com>
 */

namespace mehrkanal;


class TCPDF2DBarcodeJpeg extends \TCPDF2DBarcode {

    /**
     * Return a Jpeg image representation of barcode (requires Imagick library).
     *
     * @param int    $w Width of a single rectangle element in pixels.
     * @param int    $h Height of a single rectangle element in pixels.
     * @param array  $color
     * @param string $filename
     * @param int    $border
     *
     * @return bool
     * @public
     */
    public function createBarcodeJpg($w = 3, $h = 3, $color=[0,0,0,1], $filename = 'qr_code.jpg', $border = 3) {
	if (!extension_loaded('imagick')) {
	    return false;
	}
        $width = ($this->barcode_array['num_cols'] * $w);
        $height = ($this->barcode_array['num_rows'] * $h);
        $foreground_color = escapeshellarg('cmyk('.$color[0].'%,'.$color[1].'%,'.$color[2].'%,'.$color[3].'%)');
        $coordinates = [];
        // print barcode elements
        $y = 0;
        // for each row
        for ($row = 0; $row < $this->barcode_array['num_rows']; ++$row) {
            $x = 0;
            // for each column
            for ($column = 0; $column < $this->barcode_array['num_cols']; ++$column) {
                if ($this->barcode_array['bcode'][$row][$column] == 1) {
                    $coordinates[] = $x.','.$y.','.($x + $w - 1).','.($y + $h - 1);
                }
                $x += $w;
            }
            $y += $h;
        }

        $extent_width = ($width*($border*0.1))+$width;
        $extent_height = ($height*($border*0.1))+$height;

        $cmd = 'convert -gravity center -size '.$width.'x'.$height.' xc:white -density 300 -fill '.$foreground_color;
        foreach ($coordinates as $coordinate) {
            $cmd .= ' -draw \'rectangle '.$coordinate.'\'';
        }
        $cmd .= ' -colorspace cmyk -extent '.$extent_width.'x'.$extent_height.' '.escapeshellarg($filename);
        exec($cmd);
        return true;
    }
}
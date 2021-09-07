<?php
if (is_numeric ( $_REQUEST ['cmd'] ) && $_REQUEST ['data']) {
	$imagedata = explode ( '|', $_REQUEST ['data'] );
	if (sizeof ( $imagedata ) > 0) {
		$image = imagecreatetruecolor ( 550, 388 );
		
		foreach ( $imagedata as $img_line ) {
			$img_values = explode(',', $img_line);
			$thecolor = imagecolorallocate ( $image, $img_values[3], 0, 0 );
			imagesetpixel ( $image, $img_values[1], $img_values[2], $thecolor );
		}
			
		
//		foreach ( $_REQUEST ['x'] as $index => $x ) {
//			$thecolor = imagecolorallocate ( $image, $_REQUEST ['r'] [$index], 0, 0 );
//			imagesetpixel ( $image, $x, $_REQUEST ['y'] [$index], $thecolor );
//		}
		
		imagecolortransparent ( $image, imagecolorallocatealpha ( $image, 0, 0, 0, 127 ) );
		
		ob_start ();
		imagepng ( $image );
		$img_str = ob_get_contents ();
		ob_end_clean ();
		echo 'data:image/png;base64,' . base64_encode ( $img_str ) . '';
	}
}
?>

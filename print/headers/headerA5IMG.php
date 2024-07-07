<?php 
if(isset($_GET['color'])){
     include("../imageMagick/vendor/autoload.php");
    $color=isset($_GET['color'])?(($_GET['color'])): 0xF9F9F9;
    $darkColor="#$darkColor";
    $color="#$color";
     header("Content-Type: image/svg+xml");
    $content= file_get_contents( 'a5Header.svg' );
	$patterns = array();
	//this is A
    $patterns[0] = '{fill:#6ABD45;}';
    //this is the darken color
   // $patterns[1] = '{fill:#5F7E8C;}';
    //this is the general color
   // $patterns[2] = '{fill:#6ABD45;}';
    
    $replacements = array();
    //this is A
   // $replacements[0] = 'fill:#FFFFFF;';
    //this is the darken color
   // $replacements[1] = "fill:$darkColor;";
    //this is the general color
    $replacements[0] = "fill:$color;";
    //echo "   $color    $darkColor"; 
    $content=preg_replace($patterns, $replacements, $content);
    
    $im = new Imagick();
    $im->setResolution(300, 300);
    $im->readImageBlob($content);       
    $im->setIteratorIndex(0); // read first page
    $im->setImageFormat('jpeg');
    // $im->setImageCompression(imagick::COMPRESSION_JPEG);
//   $im->setImageCompressionQuality(100);
    $im->writeImage("sisi.jpeg");
    header('Location: sisi.jpeg');
    exit;
}
?>
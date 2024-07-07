<?php 
if(isset($_GET['color'])){
    include("../imageMagick/vendor/autoload.php");
    $color=$_GET['color'];
    $darkColor=isset($_GET['darkColor'])?$_GET['darkColor']:$color;
    $color="#$color";
    $darkColor="#$darkColor";
    
    
    // header("Content-Type: image/svg+xml");
    $content= file_get_contents( 'a4Header.svg' );
	$patterns = array();
	$replacements = array();
	
    $patterns[0] = '{fill:#7CB743;}';
    $patterns[1] = '{fill:#93C83E;}';
    $patterns[2] = '{fill:#7EB642;}';
    
    $replacements[0] = "fill:$color;";
    $replacements[1] = "fill:$darkColor;";
    $replacements[2] = "fill:$color;";
    
    $content=preg_replace($patterns, $replacements, $content);
    
    
    $im = new Imagick();
    $im->setResolution(600, 600);
    $im->readImageBlob($content);       
    $im->setIteratorIndex(0); // read first page
    $im->setImageFormat('jpeg');
    // $im->setImageCompression(imagick::COMPRESSION_JPEG);
//   $im->setImageCompressionQuality(100);
    $im->writeImage("soso.jpeg");
    header('Location: soso.jpeg');
exit;


// $name = './img/ok.png';
// $fp = fopen($name, 'rb');

// header("Content-Type: image/png");
// header("Content-Length: " . filesize($name));

// fpassthru($fp);
    
    // header('Content-Type: image/jpeg');
    
    // ob_start();
    // print $im->getImageBlob(); 
    // $contents =  ob_get_contents();
    // ob_end_clean();

    // echo "<img src='data:image/jpeg;base64,".base64_encode($contents)."' />"; //ou
echo $im->getImageBlob();
// echo '<img src="data:image/jpg;base64,' . base64_encode($im) . '"  />';
    
    // echo $content;
}
?>
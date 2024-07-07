<?php 
if(isset($_GET['color'])){
    $color=$_GET['color'];
    $darkColor=isset($_GET['darkColor'])?$_GET['darkColor']:$color;
    $color="#$color";
    $darkColor="#$darkColor";
    
    
    header("Content-Type: image/svg+xml");
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
    //$content=str_replace('INVOICE',"TESTdsadas",$content);
    echo $content;
}
?>
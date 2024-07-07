<?php 
if(isset($_GET['color']) && isset($_GET['darkColor'])){
    $color=isset($_GET['color'])?(($_GET['color'])): 0xF9F9F9;
    $darkColor=isset($_GET['darkColor'])?(($_GET['darkColor'])): 0x000000;
    $darkColor="#$darkColor";
    $color="#$color";
     header("Content-Type: image/svg+xml");
    $content= file_get_contents( 'SaffouryPaperLogo.svg' );
	$patterns = array();
	//this is A
    $patterns[0] = '{fill:#FFFFFF;}';
    //this is the darken color
    $patterns[1] = '{fill:#5F7E8C;}';
    //this is the general color
    $patterns[2] = '{fill:#809BA9;}';
    
    $replacements = array();
    //this is A
    $replacements[0] = 'fill:#FFFFFF;';
    //this is the darken color
    $replacements[1] = "fill:$darkColor;";
    //this is the general color
    $replacements[2] = "fill:$color;";
    //echo "   $color    $darkColor"; 
    $content=preg_replace($patterns, $replacements, $content);
    echo $content;
}
if(isset($_GET['stroke']) && isset($_GET['color'])){
    $color=$_GET['color'];
    $color="#$color";
    
     header("Content-Type: image/svg+xml");
    $content= file_get_contents( isset($_GET['newModel'])?'OnlySNewModel.svg':'OnlySStrokeExpanded.svg' );
	$patterns = array();
    $patterns[0] = '{fill:#60BB4E;}';
    
    $replacements = array();
    $replacements[0] = "fill:$color";

    $content=preg_replace($patterns, $replacements, $content);
    echo $content;
}
// $color='#ce2b20';
// $darkColor=$color;
// $content= file_get_contents( 'SaffouryPaperLogo.svg' );
// 	$patterns = array();
// 	//this is A
//     $patterns[0] = '{fill:#FFFFFF;}';
//     //this is the darken color
//     $patterns[1] = '{fill:#5F7E8C;}';
//     //this is the general color
//     $patterns[2] = '{fill:#809BA9;}';
    
//     $replacements = array();
//     //this is A
//     $replacements[0] = 'fill:#FFFFFF;';
//     //this is the darken color
//     $replacements[1] = "fill:$darkColor;";
//     //this is the general color
//     $replacements[2] = "fill:$color;";
    
//     $content=preg_replace($patterns, $replacements, $content);
//     echo $content;
?>

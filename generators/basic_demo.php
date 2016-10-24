<?php

require "../../../config.php";
echo ("Welcome to The Flash Demo ") ;

echo ("<div style=''>") ; 

$data = array ("boxheight" =>80,
	"boxwidth" => 200,
	"skin" => "C",
	"graphwidth" => 200,
	"stop" => "70",
	"successrate" => 90
) ;

$test_data = urlencode(json_encode($data));

require('progress_bar.php');
/* 
$data = array ("boxheight" =>120,
	"boxwidth" => 400,
	"skin" => "C",
	"graphwidth" => 200,
	"stop" => "70",
	"successrate" => 34
   ) ;

$test_data = urlencode(json_encode($data));

require('progress_bar.php');


$data = array ("boxheight" => 120,
	"boxwidth" => 700,
	"maxattempts" =>8,
	"results" => array(
		array( "date" => '12/12/2009',"success" => false),
		array( "date" => '12/12/2009',"success" => true),
		array( "date" => '14/9/2008',"success" =>true)
	)
) ;

$test_data = urlencode(json_encode($data));
require('barchen_attempts.php');


$data = array ("boxheight" => 310, "boxwidth" => 400, "stopA" => 60, "stopC" => 70, 
	"results" => array 
	(array ("graphheightA" => 85, "graphheightC" => 75, "date" => "13/2/2010" ), 

	array ("graphheightA" => 30, "graphheightC" => 70, "date" => "14/3/2010" ) ) );

    $test_data = urlencode(json_encode($data));
    require('barchen_his_chart.php');
 */

echo ("</div>");


?>
</body>
</html>
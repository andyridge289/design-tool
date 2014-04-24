<?php

random_condition();

function random_tool()
{
    $PARTICIPANTS = 50;
    $TOOLS = array("", "Tasker", "Atooma", "AutomateIt", "IFTTT", "Yahoo! Pipes", "Quartz Composer", "Automator"); // Put the names at the right index in here..
    $output = "";

    for($i = 0; $i < $PARTICIPANTS; $i++)
    {
        $output .= "\nP" . ($i + 1);
        $numbers = range(1, 7);
        shuffle($numbers);
        foreach($numbers as $number)
            $output .= "," . $TOOLS[$number];

        $output .= "\n";
        $numbers = range(1, 7);
        shuffle($numbers);
        foreach($numbers as $number)
            $output .= "," . $TOOLS[$number];
    }

    $handle = fopen("random_tools.csv", "w");
    fwrite($handle, $output);
    fclose($handle);
}

function random_condition()
{
    $PARTICIPANTS = 10;
    $COND = array(1, 2, 3, 4, 5);
    $output = "";

    for($i = 0; $i < $PARTICIPANTS; $i++)
    {
        $output .= "\nP" . ($i + 1);
        $numbers = range(0, 4);
        shuffle($numbers);

        foreach($numbers as $number)
            $output .= "," . $COND[$number];
    }

    $handle = fopen("random_conditions.csv", "w");
    fwrite($handle, $output);
    fclose($handle);
}


?>
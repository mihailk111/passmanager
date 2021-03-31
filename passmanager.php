#!/usr/bin/php
<?php

$db_path = "/home/mikhail/Desktop/programs/pass/passwords.db";

$colors = new Colors();
$db = new PDO("sqlite:$db_path");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo PHP_EOL;
start:
print_entries($db);

print_usage();
start_noshow:
echo "\ncomand: ";
$input = explode(" ", trim(fgets(STDIN)));
$comand = $input[0];

if ($comand === 'get') {

    $entries =  $db->query("SELECT * FROM entries")->fetchAll(PDO::FETCH_ASSOC);
    $entrie = $entries[$input[1]];

    $login = $entrie['login'];
    $login_array;
    $i = strlen($login) - 1;

    foreach (str_split($login) as $k => $val) {
        $login_array[$k] = "$i=>$val ";
        $i--;
    }

    $login = implode("", $login_array);
    echo "give key for [{$entrie['servise']}] {$login}: ";
    $input_key = trim(fgets(STDIN));
    echo "\nPass is: " . $colors->getColoredString(myhash($entrie['login'], $input_key), "light_green");
    echo PHP_EOL;
    echo PHP_EOL;
    goto start_noshow;
} else if ($comand === 'set') {
    $servise = $input[1];
    $login = $input[2];
    $request = $db->prepare("SELECT * FROM entries WHERE servise = ?");
    $result = $request->execute([$servise]);
    $entries = $request->fetchAll(PDO::FETCH_ASSOC);

    if (count($entries) === 1) {

        $data = ['serv' => $servise, 'login' => $login];
        $result = $db->prepare("UPDATE entries SET id=NULL, servise=:serv, login=:login WHERE servise = :serv")->execute($data);
        echo $colors->getColoredString("Updated successfully ! ($result)" . PHP_EOL . PHP_EOL, "light_green");
    } else {
        $request = $db->prepare("INSERT INTO entries VALUES (NULL, :serv, :login)");
        $result = $request->execute(['serv' => $servise, 'login' => $login]);
        echo $colors->getColoredString("Added successfully ! ({$result})"  . PHP_EOL . PHP_EOL, "light_green");
    }
    goto start;
} else if ($comand === 'rm') {
    $servise_to_remove = $db->query("SELECT * FROM entries")->fetchAll(PDO::FETCH_ASSOC)[$input[1]]['servise'];
    $r = $db->prepare("DELETE FROM entries WHERE servise=?")->execute([$servise_to_remove]);
    goto start;
} else if ($comand === 'q') {
    exit();
} else {
    echo $colors->getColoredString("\nUnknown comand: $comand", "light_red") . PHP_EOL;
    goto start;
}

function print_entries($db)
{
    foreach ($db->query("SELECT * FROM entries")->fetchAll(PDO::FETCH_ASSOC) as $k => $val) {
        echo $k . "\t";
        echo light_blue($val['servise']) . " -> ";
        echo light_green($val['login']) . PHP_EOL;
    }
}

function print_usage()
{

    echo <<< USAGE


Usage: 
  {$GLOBALS['colors']->getColoredString("get", "blue")} <{$GLOBALS['colors']->getColoredString("num", "red")}>                       - get pass
  {$GLOBALS['colors']->getColoredString("set", "blue")} <{$GLOBALS['colors']->getColoredString("servise", "red")}> <{$GLOBALS['colors']->getColoredString("login / newlogin", "red")}>- set new or change service
  {$GLOBALS['colors']->getColoredString("rm", "blue")}  <{$GLOBALS['colors']->getColoredString("num", "red")}>                       - remove 
  {$GLOBALS['colors']->getColoredString("q", "blue")}                               - exit


USAGE;
}


function light_green($string)
{
    echo $GLOBALS['colors']->getColoredString($string, "green");
}

function light_blue($string)
{
    echo $GLOBALS['colors']->getColoredString($string, "light_blue");
}

function myhash($login, $sol)
{

    $a = str_split(hash("md5", $login . $sol), 10)[0];
    $a_array = str_split($a);
    foreach ($a_array as $k => $val) 
    {
        $code = ord($val);
        if ($code >= 97 && $code <= 122) 
         {
            $a_array[$k] = strtoupper($val);
            break;
        }
    }
    $a_array[] = "_";
    $a_string = implode("", $a_array);

    return $a_string;

}

class Colors {
		private $foreground_colors = array();
		private $background_colors = array();
 
		public function __construct() {
			// Set up shell colors
			$this->foreground_colors['black'] = '0;30';
			$this->foreground_colors['dark_gray'] = '1;30';
			$this->foreground_colors['blue'] = '0;34';
			$this->foreground_colors['light_blue'] = '1;34';
			$this->foreground_colors['green'] = '0;32';
			$this->foreground_colors['light_green'] = '1;32';
			$this->foreground_colors['cyan'] = '0;36';
			$this->foreground_colors['light_cyan'] = '1;36';
			$this->foreground_colors['red'] = '0;31';
			$this->foreground_colors['light_red'] = '1;31';
			$this->foreground_colors['purple'] = '0;35';
			$this->foreground_colors['light_purple'] = '1;35';
			$this->foreground_colors['brown'] = '0;33';
			$this->foreground_colors['yellow'] = '1;33';
			$this->foreground_colors['light_gray'] = '0;37';
			$this->foreground_colors['white'] = '1;37';
 
			$this->background_colors['black'] = '40';
			$this->background_colors['red'] = '41';
			$this->background_colors['green'] = '42';
			$this->background_colors['yellow'] = '43';
			$this->background_colors['blue'] = '44';
			$this->background_colors['magenta'] = '45';
			$this->background_colors['cyan'] = '46';
			$this->background_colors['light_gray'] = '47';
		}
 
		// Returns colored string
		public function getColoredString($string, $foreground_color = null, $background_color = null) {
			$colored_string = "";
 
			// Check if given foreground color found
			if (isset($this->foreground_colors[$foreground_color])) {
				$colored_string .= "\033[" . $this->foreground_colors[$foreground_color] . "m";
			}
			// Check if given background color found
			if (isset($this->background_colors[$background_color])) {
				$colored_string .= "\033[" . $this->background_colors[$background_color] . "m";
			}
 
			// Add string and end coloring
			$colored_string .=  $string . "\033[0m";
 
			return $colored_string;
		}
 
		// Returns all foreground color names
		public function getForegroundColors() {
			return array_keys($this->foreground_colors);
		}
 
		// Returns all background color names
		public function getBackgroundColors() {
			return array_keys($this->background_colors);
		}
	}
 
?>
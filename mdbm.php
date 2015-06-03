<?php
header('Content-Type: text/html; charset=utf-8');
define('VERSION', '0.2.2');
/*
=============================
Mass Database Modifier (MDBM)
=============================

Run SQL statements on multiple databases!
MDBM is a very handy tool if you need to modify
multiple databases all at once. It's a huge help
and saves a lot of time.

Requirements:
- PHP5 with PDO extension installed and enabled

Installation & Usage:
---------------------
1. Download this file to your computer.
2. Open it with your favorite text editor.
3. Fill out parts 1-4. in the configuration block.
4. Save the file and upload it to your web server.
5. Run the file from your web browser.
6. When finished, delete the file from the server.

MDBM is created by: Dr. Balazs Zatik
You can contact me at: zatikbalazs@gmail.com

This software is 100% free and open source.
*/


// ---------------------------------------------------
// I. Configuration (you only have to edit this block)
// ---------------------------------------------------

// 1/4. Administrator's Credentials
// (user who has permission to manage all DBs)
$host = 'localhost';
$user = 'root';
$pass = 'root';

// 2/4. Select Databases That Match Regular Expression
$regexp = '/test/';
/*
Example: /^test/ matches all DBs starting with "test"
Example: /test/ matches all DBs containing "test"
Example: /test$/ matches all DBs ending with "test"
Learn more about Regular Expressions!
*/

// 3/4. Databases in This Array Will Be Ignored
$ignored = array(
	'information_schema',
	'mysql',
	'performance_schema',
	'phpmyadmin'
);

// 4/4. Statement(s) to Be Executed on All Selected DBs
$stmt = '
	CREATE TABLE People
	(
	PersonID int,
	LastName varchar(255),
	FirstName varchar(255),
	Address varchar(255),
	City varchar(255)
	);
';
/*
IMPORTANT INFO!
- The statement works with or without ticks (`) as well
- DON'T USE quotes ('), double quotes (") or delimiters
- Multiple statements can be entered, separated by (;)
*/

// ----------------------------------------------------
// II. YOU DON'T NEED TO EDIT ANYTHING BELOW THIS LINE!
// ----------------------------------------------------

// Title
echo '<h1>Mass Database Modifier v'.VERSION.'</h1>';

// Help Text
echo '<p>Help can be found in the source code of this file.<p>';

// Testing Configuration
echo '<h3>Testing Configuration:</h3>';

// Establish Connection
try
{
	$dbh = new PDO("mysql:host=$host", $user, $pass);
	echo 'OK: Admin connection established!<br />';
}
catch (PDOException $e)
{
    echo 'Error: Admin connection could not be established!<br />
    Please double check your host, username and password.<br />'
    .$e->getMessage().'<br />';
    die();
}

// Get All Databases
$result = $dbh->query('SHOW DATABASES');
if ($result)
{
	echo 'OK: All Available Databases Fetched!<br />';
}
else
{
	echo 'Error: Databases Could Not Be Fetched!<br />';
	die();
}

// Your Statement
echo '<h3>Your Statement:</h3>';
if (isset($stmt))
{
	if (trim($stmt) === '')
	{
		echo '<span style="font-weight:bold;color:#A60000;">No statement!</span>';
	}
	else
	{
		echo '<span style="font-style:italic;">'.$stmt.'</span>';
	}
}

// Selected Databases
echo '<h3>Selected Databases:</h3>';

// Set Counters
$total_db = 0;
$total_err = 0;

// Loop Through Databases
foreach($result as $row)
{
	// Get Name of DB
	$dbname = $row['Database'];

	if (!in_array($dbname, $ignored))
	{
		// Match DB Names to Regular Expression
		if (preg_match($regexp, $dbname))
		{
			++$total_db;
			echo $total_db.'. ';

			// Connect to Each Individual DB
			try
			{
				$dbh = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
				echo '"'.$dbname.'" | Connection: OK';
			}
			catch (PDOException $e)
			{
			    echo '"'.$dbname.'" | Connection: Error - '.$e->getMessage();
			    ++$total_err;
			    die();
			}

			// Run Queries Only If Requested
			if (isset($_GET['run']))
			{
				if (!$dbh->query($stmt))
				{
					$query_err = $dbh->errorInfo();
				    echo ' | <span style="color:#FFFFFF;background-color:#A60000;">
				    &nbsp;&gt;&gt; Query: Error - '.$query_err[2].'&nbsp;</span>';
				    ++$total_err;
				}
				else
				{
					echo ' | <span style="color:#FFFFFF;background-color:#018100;">
					&nbsp;&gt;&gt; Query: OK&nbsp;</span>';
				}
			}
			echo '<br />';
		}
	}
}

// If No Databases Are Found
if ($total_db === 0)
{
	echo 'No databases found with the given regular expression: '.$regexp.'<br />';
	echo 'Please check your list of ignored databases as well.';
}

// Summary (jump here after running queries)
echo '<h3 id="summary">Summary:</h3>';

// How Many Databases?
if ($total_db === 0)
{
	echo '<span style="font-weight:bold;color:#A60000;">No databases found.</span><br />';
}
else
{
	echo $total_db.' databases.<br />';
}

// How Many Errors?
if ($total_err === 0)
{
	echo '<span style="font-weight:bold;color:#018100;">No errors found.</span>';
}
else
{
	echo '<span style="font-weight:bold;color:#A60000;">'.$total_err.' errors found.</span>';
}

// Display Links
if ($total_db > 0 && $total_err === 0 && !isset($_GET['run']))
{
	echo '<br /><br />';
	echo '<a href="mdbm.php?run#summary">Run Queries!</a>';
	echo '<br /><br />';
}
else
{
	echo '<br /><br />';
	echo '<a href="mdbm.php">Go Back</a>';
	echo '<br /><br />';
}

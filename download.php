<?php
include("include/connect.php");
if(isset($_GET['download_info']))
{
	$date = date('Y-m-d G:i:s');
	header('Content-Disposition: attachment; filename="user_info_'.$date.'.xls"');
	$column = mysql_real_escape_string($_GET['column']);
	$column_value = mysql_real_escape_string($_GET['column_value']);
	$column2 = mysql_real_escape_string($_GET['column2']);
	$column_value2 = mysql_real_escape_string($_GET['column_value2']);
	$column_list = mysql_real_escape_string($_GET['column_list']);
	$fruit_pref_column = mysql_real_escape_string($_GET['fruit_pref_column']);
	$comment_column = mysql_real_escape_string($_GET['comment_column']);
	
	if($column_value2!="" && $column2!="")
	{
		$export = $db_con->prepare("SELECT ".$column_list." FROM users WHERE ".$column." = :column_value && ".$column2." = :column_value2");
		$export->bindParam(':column_value2', $column_value2);
		
		$user_id_query = $db_con->prepare("SELECT user_id FROM users WHERE ".$column." = :column_value && ".$column2." = :column_value2");
		$user_id_query->bindParam(':column_value2', $column_value2);
	}
	else
	{
		$export = $db_con->prepare("SELECT ".$column_list." FROM users WHERE ".$column." = :column_value");
		$user_id_query = $db_con->prepare("SELECT user_id FROM users WHERE ".$column." = :column_value");
	}
	
	$user_id_query->bindParam(':column_value', $column_value);
	$user_id_query->execute();
	
	while($user_id_row = $user_id_query->fetch(PDO::FETCH_ASSOC))
	{
		$loop_user_id[] = $user_id_row['user_id'];
	}
	
	$export->bindParam(':column_value', $column_value);
	$export->execute();
	
	$table_names_rows = explode(", ", $column_list);
	
	$header ="";
	$data ="";

	foreach($table_names_rows as $table_name_row)
	{
		$header .= $table_name_row. "\t";
	}
	if($fruit_pref_column==true)
		$header .= "preferred fruit \t";
	if($comment_column==true)
		$header .= "comments \t";
	
	$i = 0;
	
	//ALL NORMAL COLUMNS
	while($row = $export->fetch(PDO::FETCH_ASSOC))
	{
		$line = '';
		foreach($row as $value)
		{
			if ((!isset($value)) || ($value == "" ))
			{
				$value = "\t";
			}
			else
			{
				$value = str_replace('"', '""', $value);
				$value = '"'.$value.'"'."\t";
			}
			$line .= $value;
		}
		
		//COMMENT AND FRUIT PREFERENCE COLUMNS
		if($fruit_pref_column==true || $comment_column==true)
		{
			$user_id = $loop_user_id[$i];
			
			if($fruit_pref_column==true)
			{
				$fruit_preference_query = $db_con->prepare("SELECT fruit_name FROM fruit_preference WHERE user_id=:user_id && preference='yes' ORDER BY fruit_name");
				$fruit_preference_query->execute(array(':user_id'=>$user_id));
				
				$value = "";
				while($fruit_preference_row = $fruit_preference_query->fetch(PDO::FETCH_ASSOC))
				{
					$value .= $fruit_preference_row['fruit_name'].", ";
				}

				if ((!isset($value)) || ($value == "" ))
				{
					$value = "\t";
				}
				else
				{
					$value = str_replace('"', '""', $value);
					$value = '"'.$value.'"'."\t";
				}
				$line .= $value;
			}
			
			if($comment_column==true)
			{
				$comment_query = $db_con->prepare("SELECT comment FROM comments WHERE user_id=:user_id ORDER BY comment");
				$comment_query->execute(array(':user_id'=>$user_id));
				
				$value = "";
				while($comment_row = $comment_query->fetch(PDO::FETCH_ASSOC))
				{
					$value .= $comment_row['comment'].", ";
				}
				
				if ((!isset($value)) || ($value == "" ))
				{
					$value = "\t";
				}
				else
				{
					$value = str_replace('"', '""', $value);
					$value = '"'.$value.'"'."\t";
				}
				$line .= $value;
			}
		}
		$data .= trim($line) . "\n";
		$i++;
	}
	
	$data = str_replace("\r", "", $data);

	if ($data == "")
	{
		$data = "\n(0) Records Found!\n";                        
	}
	print "$header\n$data";
}
exit();
?>
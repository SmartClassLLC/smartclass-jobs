<?php

class sql_db
{

        var $db_connect_id;
        var $query_result;
        var $row = array();
		var $result_row = array();
        var $rowset = array();
        var $num_queries = 0;
        var $set_of_queries = array();

        //
        // Constructor
        //
        function sql_db($sqlserver, $sqluser, $sqlpassword, $database, $persistency = true)
        {

                $this->persistency = $persistency;
                $this->user = $sqluser;
                $this->password = $sqlpassword;
                $this->server = $sqlserver;
                $this->dbname = $database;

                if($this->persistency)
                {
                        $this->db_connect_id = @mysql_pconnect($this->server, $this->user, $this->password);
                }
                else
                {
                        $this->db_connect_id = @mysql_connect($this->server, $this->user, $this->password);
                }
                if($this->db_connect_id)
                {
                        if($database != "")
                        {
                                $this->dbname = $database;
                                $dbselect = @mysql_select_db($this->dbname);
                                if(!$dbselect)
                                {
                                        @mysql_close($this->db_connect_id);
                                        $this->db_connect_id = $dbselect;
                                }
                        }
                        return $this->db_connect_id;
                }
                else
                {
                        return false;
                }
        }

        //
        // Other base methods
        //
        function sql_close()
        {
                if($this->db_connect_id)
                {
                        if($this->query_result)
                        {
                                @mysql_free_result($this->query_result);
                        }
                        $result = @mysql_close($this->db_connect_id);
                        return $result;
                }
                else
                {
                        return false;
                }
        }

        //
        // Base query method
        //
        function sql_query($query = "", $transaction = FALSE)
        {
             // Remove any pre-existing queries
             unset($this->query_result);
             if($query != "")
             {
               	$this->query_result = @mysql_query($query, $this->db_connect_id);
				$this->save_queries($query);
             }
             if($this->query_result)
             {
                     unset($this->row[$this->query_result]);
                     unset($this->rowset[$this->query_result]);
                     return $this->query_result;
             }
             else
             {
                     //return ( $transaction == END_TRANSACTION ) ? true : false;
                     return false;
             }
        }

        //
        // Other query methods
        //
        function sql_numrows($query_id = 0)
        {
                if(!$query_id)
                {
                        $query_id = $this->query_result;
                }
                if($query_id)
                {
                        $result = @mysql_num_rows($query_id);
                        return $result;
                }
                else
                {
                        return false;
                }
        }
        function sql_affectedrows()
        {
                if($this->db_connect_id)
                {
                        $result = @mysql_affected_rows($this->db_connect_id);
                        return $result;
                }
                else
                {
                        return false;
                }
        }
        function sql_numfields($query_id = 0)
        {
                if(!$query_id)
                {
                        $query_id = $this->query_result;
                }
                if($query_id)
                {
                        $result = @mysql_num_fields($query_id);
                        return $result;
                }
                else
                {
                        return false;
                }
        }
        function sql_fieldname($offset, $query_id = 0)
        {
                if(!$query_id)
                {
                        $query_id = $this->query_result;
                }
                if($query_id)
                {
                        $result = @mysql_field_name($query_id, $offset);
                        return $result;
                }
                else
                {
                        return false;
                }
        }
		
		function sql_fieldnamearray($query = false)
		{
			$names = array();
	
			$field = $this->sql_numfields($query);
	
			for ( $i = 0; $i < $field; $i++ )
			{
				$names[] = $this->sql_fieldname($i, $query);
			}
	
			return $names;
		}

        function sql_fieldtype($offset, $query_id = 0)
        {
                if(!$query_id)
                {
                        $query_id = $this->query_result;
                }
                if($query_id)
                {
                        $result = @mysql_field_type($query_id, $offset);
                        return $result;
                }
                else
                {
                        return false;
                }
        }
		
		
        function sql_fetchrow($query_id = 0, $result_type = MYSQL_BOTH)
        {
    		if(!$query_id)
    		{
				$query_id = $this->query_result;
    		}
    		
    		if($query_id)
    		{
				$this->row[$query_id] = @mysql_fetch_row($query_id, $result_type);						
				return $this->row[$query_id];
    		}
    		else
    		{
				return false;
    		}
        }
		
		
        function sql_fetchrowset($query_id = 0, $result_type = MYSQL_BOTH)
        {
                if(!$query_id)
                {
                        $query_id = $this->query_result;
                }
                if($query_id)
                {
                        unset($this->rowset[$query_id]);
                        unset($this->row[$query_id]);
						unset($this->result_row);
                        while($this->rowset[$query_id] = @mysql_fetch_row($query_id, $result_type))
                        {
							$this->result_row[] = $this->rowset[$query_id];
                        }
                        return $this->result_row;
                }
                else
                {
                        return false;
                }
        }
		
		
        function sql_fetchfield($field, $rownum = -1, $query_id = 0)
        {
                if(!$query_id)
                {
                        $query_id = $this->query_result;
                }
                if($query_id)
                {
                        if($rownum > -1)
                        {
                                $result = @mysql_result($query_id, $rownum, $field);
                        }
                        else
                        {
                                if(empty($this->row[$query_id]) && empty($this->rowset[$query_id]))
                                {
                                        if($this->sql_fetchrow())
                                        {
                                                $result = $this->row[$query_id][$field];
                                        }
                                }
                                else
                                {
                                        if($this->rowset[$query_id])
                                        {
                                                $result = $this->rowset[$query_id][$field];
                                        }
                                        else if($this->row[$query_id])
                                        {
                                                $result = $this->row[$query_id][$field];
                                        }
                                }
                        }
                        return $result;
                }
                else
                {
                        return false;
                }
        }
		
		
        function sql_rowseek($rownum, $query_id = 0){
                if(!$query_id)
                {
                        $query_id = $this->query_result;
                }
                if($query_id)
                {
                        $result = @mysql_data_seek($query_id, $rownum);
                        return $result;
                }
                else
                {
                        return false;
                }
        }
		
		
        function sql_nextid(){
                if($this->db_connect_id)
                {
                        $result = @mysql_insert_id($this->db_connect_id);
                        return $result;
                }
                else
                {
                        return false;
                }
        }
		
		
        function sql_freeresult($query_id = 0){
                if(!$query_id)
                {
                        $query_id = $this->query_result;
                }

                if ( $query_id )
                {
                        unset($this->row[$query_id]);
                        unset($this->rowset[$query_id]);

                        @mysql_free_result($query_id);

                        return true;
                }
                else
                {
                        return false;
                }
        }
		
		
        function sql_error($query_id = 0)
        {
                //$result["message"] = @mysql_error($this->db_connect_id);
                //$result["code"] = @mysql_errno($this->db_connect_id);

                return @mysql_error($this->db_connect_id);
        }

		function table_exists ($table, $db)
		{
			$tables = @mysql_list_tables ($db); 
			while (list ($temp) = @mysql_fetch_array ($tables))
			{
				if ($temp == $table) return true;
			}
			return false;
		}
		
		function column_exists($database, $table, $column)
		{
			$exists = false;
			$columns = @mysql_query("SHOW COLUMNS FROM ".$database.".".$table);
			while($c = @mysql_fetch_assoc($columns))
			{
				if($c['Field'] == $column)
				{
					$exists = true;
					break;
				}
			}      
			return $exists;
		}
	
		function add_column_if_not_exist($database, $table, $column, $column_attr = "VARCHAR( 255 ) NULL" )
		{
			$exists = false;
			$columns = @mysql_query("SHOW COLUMNS FROM ".$database.".".$table);
			while($c = @mysql_fetch_assoc($columns))
			{
				if($c['Field'] == $column)
				{
					$exists = true;
					break;
				}
			}      
			if(!$exists) @mysql_query("ALTER TABLE ".$database.".".$table." ADD `".$column."` ".$column_attr);
		}
	
		function save_queries($query)
		{
			$this->set_of_queries[] = $query;
		}

		function print_queries()
		{
			$allQueries = $this->set_of_queries;
			for($i=count($allQueries)-1; $i>=0; $i--)
			{
				echo $allQueries[$i]."<br>";
			}
		}

}

?>

<?php
/***************************************************************************
 *                                 mysqli.php
 *                            -------------------
 *   begin                : 1 janvier 2013
 *   copyright            : achaab & BoBmArLeY
 *   sebsite              : http://premod-shdow.servhome.org
 *
 *   $Id: mysqli.php,v 1.0 2013/01/01 21:13:47 achaab Exp $
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   Please, if you do any change to improve the code, send me an email
 *   at achaab@hotmail.fr so i can do the changes myself and diffuse them
 *
 ***************************************************************************/
 
if(!defined("SQL_LAYER"))
{
	define("SQL_LAYER","mysqli");

	class sql_db
	{

		var $db_connect_id;
		var $query_result;
		var $row = array();
		var $rowset = array();
		var $num_queries = 0;
		var $in_transaction = 0;

		//
		// Constructor
		//
		function sql_db($sqlserver, $sqluser, $sqlpassword, $database, $port = false, $persistency = false)
		{
			$this->persistency = (version_compare(PHP_VERSION, '5.3.0', '>=')) ? $persistency : false;
			$this->user = $sqluser;
			
			$this->password = $sqlpassword;
			$this->server = ($this->persistency) ? 'p:' . (($sqlserver) ? $sqlserver : 'localhost') : $sqlserver;
			
			$this->dbname = $database;
			$port = (!$port) ? NULL : $port;

			$this->db_connect_id = @mysqli_connect($this->server, $this->user, $this->password, $this->dbname, $port);
			
			if( $this->db_connect_id && $database != '')
			{
				@mysqli_query($this->db_connect_id, "SET NAMES 'ISO-8859-1'");
				
				$this->dbname = $database;
				$dbselect = @mysqli_select_db($this->db_connect_id, $this->dbname);

				if( $dbselect === false )
				{
					@mysqli_close($this->db_connect_id);
					$this->db_connect_id = $dbselect;
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
			if( $this->db_connect_id )
			{
				//
				// Commit any remaining transactions
				//
				if( $this->in_transaction )
				{
					@mysqli_commit($this->db_connect_id);
				}

				return @mysqli_close($this->db_connect_id);
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
			//
			// Remove any pre-existing queries
			//
			unset($this->query_result);

			if( $query != "" )
			{
				$this->num_queries++;
				if( $transaction == BEGIN_TRANSACTION && !$this->in_transaction )
				{
					$result = @mysqli_commit($this->db_connect_id);
					if(!$result)
					{
						return false;
					}
					$this->in_transaction = TRUE;
				}

				$this->query_result = @mysqli_query($this->db_connect_id, $query);
			}
			else
			{
				if( $transaction == END_TRANSACTION && $this->in_transaction )
				{
					$result = @mysqli_commit($this->db_connect_id);
				}
			}

			$this->query_result = (isset($this->query_result)) ? $this->query_result : false;
			if ($this->query_result)
			{
				unset($this->row[(bool)$this->query_result]);
				unset($this->rowset[(bool)$this->query_result]);

				if( $transaction == END_TRANSACTION && $this->in_transaction )
				{
					$this->in_transaction = FALSE;

					if ( !@mysqli_commit($this->db_connect_id) )
					{
						@mysqli_rollback($this->db_connect_id);
						return false;
					}
				}
				
				return $this->query_result;
			}
			else
			{
				if( $this->in_transaction )
				{
					@mysqli_rollback($this->db_connect_id);
					$this->in_transaction = FALSE;
				}
				return false;
			}
		}

		//
		// Other query methods
		//
		function sql_numrows($query_id = 0)
		{			
			if ($query_id === false)
			{
				$query_id = $this->query_result;
			}
			

			return ( $query_id ) ? @mysqli_num_rows($query_id) : false;
		}

		function sql_affectedrows()
		{
			return ( $this->db_connect_id ) ? @mysqli_affected_rows($this->db_connect_id) : false;
		}

		function sql_numfields($query_id = 0)
		{
			if ($query_id === false)
			{
				$query_id = $this->query_result;
			}

			return ( $query_id ) ? @mysqli_field_count($query_id) : false;
		}

		function sql_fieldname($offset, $query_id = 0)
		{
			if ($query_id === false)
			{
				$query_id = $this->query_result;
			}
			
			$field_info = @mysqli_fetch_field_direct($query_id, $offset);
			
			return ( $query_id ) ? $field_info->name : false;
		}

		function sql_fieldtype($offset, $query_id = 0)
		{
			if ($query_id === false)
			{
				$query_id = $this->query_result;
			}

			$field_info = @mysqli_fetch_field_direct($query_id, $offset);
			
			return ( $query_id ) ? $field_info->type : false;
		}
		
		function sql_fetchrow($query_id = 0)
		{			
			if ($query_id === false)
			{
				$query_id = $this->query_result;
			}

			if( $query_id )
			{
				$this->row[(bool)$query_id] = @mysqli_fetch_array($query_id, MYSQLI_ASSOC);
				return $this->row[(bool)$query_id];
			}
			else
			{
				return false;
			}
		}

		function sql_fetchrowset($query_id = 0)
		{			
			if ($query_id === false)
			{
				$query_id = $this->query_result;
			}

			if( $query_id )
			{
				unset($this->rowset[(bool)$query_id]);
				unset($this->row[(bool)$query_id]);

				while($this->rowset[(bool)$query_id] = @mysqli_fetch_array($query_id, MYSQLI_ASSOC))
				{
					$result[] = $this->rowset[(bool)$query_id];
				}

				if (isset($result))
				{
					return $result;
				}
				else
				{
					return false;
				} 
			}
			else
			{
				return false;
			}
		}
		
		function mysqlx_result($query_id, $rownum, $field) 
		{
			$i = 0;
			$retval = '';
			while ($row = $query_id->fetch_array(MYSQLI_BOTH)) 
			{
				if ($i == $rownum)
				{
					$retval = $row[$field];
				}
				$i++;
			}
			return $retval;
		} 

		function sql_fetchfield($field, $rownum = -1, $query_id = 0)
		{
			if ($query_id === false)
			{
				$query_id = $this->query_result;
			}

			if( $query_id )
			{
				if( $rownum > -1 )
				{
					$result = @mysqlx_result($query_id, $rownum, $field);
				}
				else
				{
					if( empty($this->row[(bool)$query_id]) && empty($this->rowset[(bool)$query_id]) )
					{
						if( $this->sql_fetchrow() )
						{
							$result = $this->row[(bool)$query_id][$field];
						}
					}
					else
					{
						if( $this->rowset[(bool)$query_id] )
						{
							$result = $this->rowset[(bool)$query_id][0][$field];
						}
						else if( $this->row[(bool)$query_id] )
						{
							$result = $this->row[(bool)$query_id][$field];
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

		function sql_rowseek($rownum, $query_id = 0)
		{
			if ($query_id === false)
			{
				$query_id = $this->query_result;
			}
			return ( $query_id ) ? @mysqli_data_seek($query_id, $rownum) : false;
		}

		function sql_nextid()
		{
			return ( $this->db_connect_id ) ? @mysqli_insert_id($this->db_connect_id) : false;
		}

		function sql_freeresult($query_id = 0)
		{			
			if ($query_id === false)
			{
				$query_id = $this->query_result;
			}

			if ( $query_id === true)
			{
				unset($this->row[(bool)$query_id]);
				unset($this->rowset[(bool)$query_id]);

				@mysqli_free_result($query_id);

				return true;
			}
			else
			{
				return false;
			}
		}

		function sql_error()
		{
			$result['message'] = @mysqli_error($this->db_connect_id);
			$result['code'] = @mysqli_errno($this->db_connect_id);

			return $result;
		}

	} // class sql_db

} // if ... define

?>
<?php
/*
 * /library/Doctrine/Doctrine/Manager.php
 */

function _buildDsnPartsArray($dsn)
{
	// fix sqlite dsn so that it will parse correctly
	$dsn = str_replace("////", "/", $dsn);
	$dsn = str_replace("\\", "/", $dsn);
	$dsn = preg_replace("/\/\/\/(.*):\//", "//$1:/", $dsn);

	// silence any warnings
	$parts = @parse_url($dsn);

	$names = array('dsn', 'scheme', 'host', 'port', 'user', 'pass', 'path', 'query', 'fragment');

	foreach ($names as $name) {
		if ( ! isset($parts[$name])) {
			$parts[$name] = null;
		}
	}

	if (count($parts) == 0 || ! isset($parts['scheme'])) {
		throw new Doctrine_Manager_Exception('Could not parse dsn');
	}

	return $parts;
}

function parseDsn($dsn)
{
	$parts = _buildDsnPartsArray($dsn);

	switch ($parts['scheme']) {
		case 'sqlite':
		case 'sqlite2':
		case 'sqlite3':
			if (isset($parts['host']) && $parts['host'] == ':memory') {
				$parts['database'] = ':memory:';
				$parts['dsn']      = 'sqlite::memory:';
			} else {
				//fix windows dsn we have to add host: to path and set host to null
				if (isset($parts['host'])) {
					$parts['path'] = $parts['host'] . ":" . $parts["path"];
					$parts['host'] = null;
				}
				$parts['database'] = $parts['path'];
				$parts['dsn'] = $parts['scheme'] . ':' . $parts['path'];
			}

			break;

		case 'mssql':
		case 'dblib':
			if ( ! isset($parts['path']) || $parts['path'] == '/') {
				throw new Doctrine_Manager_Exception('No database available in data source name');
			}
			if (isset($parts['path'])) {
				$parts['database'] = substr($parts['path'], 1);
			}
			if ( ! isset($parts['host'])) {
				throw new Doctrine_Manager_Exception('No hostname set in data source name');
			}

			$parts['dsn'] = $parts['scheme'] . ':host='
					. $parts['host'] . (isset($parts['port']) ? ':' . $parts['port']:null) . ';dbname='
							. $parts['database'];

			break;

		case 'mysql':
		case 'oci8':
		case 'oci':
		case 'pgsql':
		case 'odbc':
		case 'mock':
		case 'oracle':
			if ( ! isset($parts['path']) || $parts['path'] == '/') {
				throw new Doctrine_Manager_Exception('No database available in data source name');
			}
			if (isset($parts['path'])) {
				$parts['database'] = substr($parts['path'], 1);
			}
			if ( ! isset($parts['host'])) {
				throw new Doctrine_Manager_Exception('No hostname set in data source name');
			}

			$parts['dsn'] = $parts['scheme'] . ':host='
					. $parts['host'] . (isset($parts['port']) ? ';port=' . $parts['port']:null) . ';dbname='
							. $parts['database'];

			break;
		default:
			$parts['dsn'] = $dsn;
	}

	return $parts;
}

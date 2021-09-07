<?php
Doctrine_Manager::getInstance()->bindComponent('ClientFileUpload', 'SYSDAT');

class ClientFileUpload extends BaseClientFileUpload
{

    public function getClientFiles($clientid, $tabname = false, $folder = false)
    {
        $client_files = Doctrine_Query::create()->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
			            AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
            ->from('ClientFileUpload')
            ->where('clientid="' . $clientid . '"');
        
        if ($tabname) {
            if (is_array($tabname)) {
                $client_files->andWhereIn('tabname', $tabname);
            } else {
                $client_files->andWhere('tabname = ? ', $tabname);
            }
        } else {
            // get files without tabname
            $client_files->andWhere("tabname = ''");
        }
        
        if ($folder !== false) {
            $client_files->andWhere('folder = ?', $folder);
        } else {
            // get files without folder
            $client_files->andWhere("folder = '0'");
        }
        
        $filearray = $client_files->fetchArray();
        
        return $filearray;
    }

    public function getClientFiles2folders($clientid = 0)
    {
        $client_files = Doctrine_Query::create()->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
			            AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
            ->from('ClientFileUpload')
            ->where('clientid = ?', $clientid)
            ->andWhere("tabname = ''")
            ->andwhere('isdeleted = 0');
        $filearray = $client_files->fetchArray();
        
        foreach ($filearray as $k => $cf) {
            $files2group[$cf['folder']][] = $cf;
        }
        
        return $files2group;
    }

    public function get_client_files_sorted($clientid, $tabname = false)
    {
        $client_files = Doctrine_Query::create()->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
			            AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
            ->from('ClientFileUpload')
            ->where('clientid = ?', $clientid);
        
        if ($tabname) {
            if (is_array($tabname)) {
                $client_files->andWhereIn('tabname', $tabname);
            } else {
                $client_files->andWhere('tabname = ?', $tabname);
            }
        } else {
            // get files without tabname
            $client_files->andWhere("tabname = ''");
        }
        
        $client_files->OrderBy("create_date, recordid ASC");
        $filearray = $client_files->fetchArray();
        
        return $filearray;
    }

    public function get_latest_on_top_client_files($clientid, $tabname = false)
    {
        $client_files = Doctrine_Query::create()->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
			            AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")
            ->from('ClientFileUpload')
            ->where('clientid="' . $clientid . '"');
        
        if ($tabname) {
            if (is_array($tabname)) {
                $client_files->andWhereIn('tabname', $tabname);
            } else {
                $client_files->andWhere('tabname = ?', $tabname);
            }
        } else {
            // get files without tabname
            $client_files->andWhere("tabname = ''");
        }
        
        $client_files->OrderBy("create_date DESC");
        $filearray = $client_files->fetchArray();
        
        return $filearray;
    }

    public function get_client_files_recordid($clientid = false, $tabname = false, $recordid = false)
    {
        $client_files = Doctrine_Query::create()->select("*,AES_DECRYPT(title,'" . Zend_Registry::get('salt') . "') as title,
			            AES_DECRYPT(file_name,'" . Zend_Registry::get('salt') . "') as file_name,
						AES_DECRYPT(file_type,'" . Zend_Registry::get('salt') . "') as file_type")->from('ClientFileUpload');
        
        if ($clientid !== false) {
            
            $client_files->where('clientid="' . $clientid . '"');
        }
        
        if ($recordid) {
            if (is_array($recordid)) {
                $client_files->andWhereIn('recordid', $recordid);
            } else {
                $client_files->andWhere('recordid = ?', $recordid);
            }
        }
        
        if ($tabname) {
            if (is_array($tabname)) {
                $client_files->andWhereIn('tabname', $tabname);
            } else {
                $client_files->andWhere('tabname = ?', $tabname);
            }
        } else {
            // get files without tabname
            $client_files->andWhere("tabname = ''");
        }
        
        $client_files->OrderBy("create_date, recordid ASC");
        $filearray = $client_files->fetchArray();
        
        return $filearray;
    }

    /**
     * fn will return file infos from dbf, not the actual file from ftp
     * Jul 24, 2017 @claudiu
     *
     * @param array $ids            
     * @return multitype:|multitype:Ambigous <multitype:, Doctrine_Collection>
     */
    public static function get_files_by_id($ids = array())
    {
        $result = array();
        
        if (empty($ids) || ! is_array($ids)) {
            return $result;
        }
        
        $salt = Zend_Registry::get('salt');
        
        $result = Doctrine_Query::create()->select("*,
				AES_DECRYPT( title, '" . $salt . "' )  as title_decrypted,
				AES_DECRYPT( file_name, '" . $salt . "' )  as file_name_decrypted,
				AES_DECRYPT( file_type, '" . $salt . "' )  as file_type_decrypted")
            ->from('ClientFileUpload indexBy id')
            ->whereIn('id', $ids)
            ->fetchArray();
        
        
        return $result;
    }
}

?>
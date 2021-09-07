<?php 
// App::uses('ClassRegistry', 'Utility');

// App::build(array('Vendor' => array(APP . 'Vendor' . DS . 'phpdocx' . DS . 'classes' . DS )));
// //App::uses('CreateDocxFromTemplate', 'Vendor');
// App::import('Vendor', 'CreateDocx', array('file' => 'CreateDocx.inc'));

// App::import('Vendor', 'CreateDocxFromTemplate', array('file' => 'CreateDocxFromTemplate.inc'));

// App::import('Vendor', 'MultiMerge', array('file' => 'MultiMerge.inc'));


// App::uses( 'TemplateFolder', 'Model' );
// App::uses( 'Files', 'Model' );


// App::uses( 'FilesHelper', 'Lib' );
// App::uses( 'IndexFilesLib', 'Lib' );
/**
 * 
 * @author claudiu
 *
 *	This class was Made By Andrei@originalware, please contact him for support
 *
 */

class Pms_DocUtil
{
		
	CONST _default_font_family = "DejaVu Sans";
	
	private static function setFlash($message, $element = 'default', $params = array(), $key = 'flash') {
		CakeSession::write('Message.' . $key, compact('message', 'element', 'params'));
	}
	
	public static function handleError($error) 
	{
		//echo print_r( $error, true );

		$url = Configure::read('docutil_redirect');
		
		self::setFlash( __('Invalid file, please upload valid file.'), 'alert-type');
				
		if( $error instanceof FatalErrorException || $error instanceof InternalErrorException)
		{			
			?>
			<script type="text/javascript">

			window.location = '<?php echo $url;?>';
			
			</script>
			<?php							
		}						
	}
	
	public static function handleErrorUsage($error)
	{				
		CakeLog::error( 'DocUtil::handleErrorUsage error=' . print_r($error, true) . "\n\n" );
			
		if( $error instanceof FatalErrorException || $error instanceof InternalErrorException)
		{
			CakeLog::error( 'DocUtil::handleErrorUsage error: ' . $error->getMessage() . "\n\n" . 
					
			'code: ' . $error->getCode() . "\n\n" .
			'file: ' . $error->getFile() . "\n\n" .
			'line: ' . $error->getLine() . "\n\n" .
			'trace: ' . $error->getTraceAsString() . "\n\n"
			);
			
		}
								
		return true;
	}
	
	public function sign_pdf()
	{
		//require_once 'classes/SignPDF.inc';
		
		$sign = new SignPDF();
		
		///home/andrei/workspace/ethics/app/webroot/
		
		$sign->setPDF('/home/andrei/workspace/ethics/app/tmp/logs/token_list.pdf');
		$sign->setPrivateKey('../tmp/logs/tcpdf.crt', 'tcpdfdemo');
		$sign->setX509Certificate('../tmp/logs/tcpdf.crt');
		
		$info = array(
				'Name' => 'TCPDF',
				'Location' => 'Office',
				'Reason' => 'Testing TCPDF',
				'ContactInfo' => 'http://www.tcpdf.org'
		);
		
		$sign->sign('/home/andrei/workspace/ethics/app/tmp/logs/token_list_signed.pdf', $info);
		
	}
	
	public function sign_docx()
	{

		$sign = new SignDocx();
		
		$sign->setDocx('/home/andrei/workspace/ethics/app/tmp/logs/asd.docx');
		
		$sign->setPrivateKey('../tmp/logs/key.crt');
		$sign->setX509Certificate('../tmp/logs/cert.crt');
		
		$sign->setSignatureComments('This document has been signed by me');
		
		$sign->sign();
		
	}
	
	public function verify_file( $file, $redirect_url )
	{		
		$old_debug = Configure::read('debug');
		
		if ( is_uploaded_file($file) )
		{
			if ( !copy( $file , $file . '.docx' ) )
			{
				return array(
					'status' => false,
					'error' => __('Error verifying uploaded file')
				);				
			}
			
			$file .= '.docx';
		}
		
		try 
		{			
			Configure::write('debug', 0);
			Configure::write('docutil_redirect', $redirect_url);			
			Configure::write('Exception.handler', 'DocUtil::handleError');
			
			$docx = new CreateDocxFromTemplate( $file );

			
		}		
		catch( Exception $e )
		{	
			Configure::write('debug', $old_debug);
			return array(
				'status' => false,
				'error' => $e->getMessage()
			);			
		}
		
			
						
		Configure::write('debug', $old_debug);
		
		return array(
			'status' => true
		);
	}
	
	public function convert_docx2pdf( $docx_string, $file_id )
	{
	
		$files_helper = new FilesHelper();
		//$doc_util = new DocUtil();
		
		$template_file = $files_helper->create_temp_file(array(
			'file_name' => 'User_tmp_'.$file_id.'.docx',
			'file_blob' => $docx_string
		));
		
// 		CakeLog::debug( 'DocUtil::convert_docx2pdf template_file=' . $template_file );
		
					
		if ( isset($template_file) && !empty($template_file) && file_exists( $template_file ) )
		{
			/*
			try 
			{
				$docx = $doc_util->open_template($template_file);				
			} 
			catch (Exception $e) 
			{
				CakeLog::error( 'DocUtil::convert_docx2pdf open docx error: ' . $e->getMessage() . "\n\n" . $e->getTraceAsString() );
				
				
				return ;
			}
			*/
		}
		else
		{
			$docx = false;
			
			throw new Exception( __('Error opening file.') );
			return ;
		}
				
		
		$output_file = $files_helper->get_tmp_dir() . DS . 'User_tmp_' . $file_id;
		 
// 		CakeLog::debug( 'DocUtil::convert_docx2pdf output_file=' . $output_file );
		
		try 
		{
// 			$doc_res = $this->get_file($docx, $output_file, $files_helper->get_tmp_dir(), 'pdf');
			
			$doc_res = $this->lowlevel_convert_docx_to_pdf($output_file, $files_helper->get_tmp_dir(), 'pdf');
			
		} 
		catch (Exception $e) 
		{
			$doc_res = false;
			
			CakeLog::error( 'DocUtil::convert_docx2pdf error save: ' . $e->getMessage() . "\n\n" . $e->getTraceAsString() );
		}
		
		if ( $doc_res === false )
		{
			throw new Exception( __('Error generating document, please try again.') );
			return ;
		}
	
		$output_string = file_get_contents( $output_file . '.pdf' );
		
		$files_helper->delete( $output_file . '.docx' );
		$files_helper->delete( $output_file . '.pdf' );
				
		return $output_string;			
	}
	
	public function get_all_templates() 
	{
		$TemplateFolder = ClassRegistry::init( 'TemplateFolder' );
		$Files = ClassRegistry::init( 'Files' );
		
		//get all templates
		$db = $TemplateFolder->getDataSource();
			
		$conditionsSubQuery = array(
				'lft <= aa.lft',
				'rght >= aa.rght',
				'deleted = "no"'
		);
			
		$subQuery = $db->buildStatement(
				array(
						'fields'     => array("group_concat( `TemplateFolder`.`name` SEPARATOR ' >> ' )"),
						'table'      => $db->fullTableName($TemplateFolder, true, false),
						'alias'      => 'TemplateFolder',
						'conditions' => $conditionsSubQuery,
						'order'      => array('lft'),
						'group'      => array('deleted')
				),
				$TemplateFolder
		);
			
		
		$Files->virtualFields['path'] = $subQuery;
			
		$join1 = array(
				'table' => 'template_folders',
				'alias' => 'aa',
				'type' => 'LEFT',
				'conditions' => array(
						'Files.template_type = aa.id'
				)
		);
			
			
		$letter_templates = $Files->find('all', array(
				'conditions' => array(
						'Files.type' => 'letter_template',
						'Files.deleted' => 'no'
				),
				'joins' => array($join1),
				'order' => array('Files.path' => 'ASC', 'Files.name' => 'ASC')				
				
		));
					
// 		pr($letter_templates);
		
		return $letter_templates;
	}
	
	public function generate_temp_file( $template_file, $project_info, $user_info, $gentype = 'docx' )
	{
		if ( isset($template_file) && !empty($template_file) && file_exists( $template_file ) )
		{
			$docx = $this->open_template($template_file);
		}
		else
		{
			$docx = false;
		}
		
		if ( $docx === false )
		{
			throw new Exception( __('Error opening template file.') );			
		}
		
		App::uses( 'Tokens', 'Lib' );
		App::uses( 'FilesHelper', 'Lib' );
		
		$files_helper = new FilesHelper();
		$tokens = new Tokens();
		
		$vars = $this->getTemplateVariables( $docx );
		
		
		
		$select_tokens = array();
		$html_tokens = array();
			
		if ( !empty($vars) )
		{
			$tTokens = $tokens->parse_docx_tokens($vars);
		
			foreach( $tTokens as $token )
			{
				
				if ( $tokens->isMultiple($token, $project_info, $user_info ) )
				{				
					$select_tokens[ $token ] = '';
				}
				else
				{
					$select_tokens[ $token ] = $tokens->getTokenValue($token, $project_info, $user_info );
					
					//$select_tokens[ $token ] = 
					
					$html_tokens[ $token ] = $tokens->getTokenValue($token, $project_info, $user_info, true);
			
					if( is_null( $select_tokens[$token] ))
					{
						unset($select_tokens[$token]);
					}
					
					if( is_null( $html_tokens[$token] ) )
					{
						unset( $html_tokens[$token] );
					}
				}
				
			}
		}
		
		//pr( $select_tokens );
			
		$old_debug = Configure::read('debug');
		Configure::write('debug', 0);
		
		$doc_res = $this->process_template($docx, $select_tokens, $html_tokens);
		
		Configure::write('debug', $old_debug);
		
		
		if ( $doc_res === false )
		{
			throw new Exception( __('Error replacing tokens in document, please try again.') );			
		}
			
		$output_file = $files_helper->get_tmp_dir() . DS . 'tempI' . $user_info['id'] . (isset($project_info['Project']['id']) ? $project_info['Project']['id'] : $project_info['Meeting']['id']);
			
		//pr( $output_file );
		
		//return;
		
		$doc_res = $this->get_file($docx, $output_file, $files_helper->get_tmp_dir(), $gentype);
			
		if ( $doc_res === false )
		{
			throw new Exception( __('Error generating document, please try again.') );			
		}
		
		return $output_file . '.' . $gentype;
					
	}
	
	public function get_template_file( $res, $user_id )
	{
		$files_helper = new FilesHelper();
		
		$template_file = '';
			
		if ( $res['storage'] == 'mysql' && isset( $res['file_name'] ) && isset( $res['file_blob'] ) )
		{
			$filename = $files_helper->get_tmp_dir() . DS . 'tmp' . $user_id . $res['file_name'];
		
			file_put_contents( $filename, $res['file_blob'] );
		
			$template_file = $filename;
		}
		elseif ( isset($res['file_real_name']) && !empty($res['file_real_name']) )
		{
			$template_file = $res['file_real_name'];
		}
		
		return $template_file;
	}
	
	public function merge_docx( $temp_files, $output_file )
	{
		//pr( $output_file );
		$old_debug = Configure::read('debug');
		
// 		CakeLog::error( 'temp_files= ' .print_r($temp_files, true) );
		
		$first = array_shift( $temp_files );
		
		
		$merge = new MultiMerge();

// 		CakeLog::error( 'first=' . print_r($first, true) );
// 		CakeLog::error( 'temp_files= ' .print_r($temp_files, true) );
		
		if ( empty($temp_files) )
		{
			copy($first, $output_file);
		}
		else
		{
			try 
			{			
				Configure::write('debug', 0);
				$merge->mergeDocx($first, $temp_files, $output_file, array(
					'enforceSectionPageBreak' => true,
					'mergeType' => 0,
					'forceLatestStyles' => true
				));
			}
			catch( Exception $e )
			{
				Configure::write('debug', $old_debug);
				throw new Exception( __('Error merging documents') );
			}
		}
		
		Configure::write('debug', $old_debug);
		
		return true;
	}
	
	public function open_template( $file, $symbol = "$" )
	{
		$sym = Configure::read('template_symbol');
		
		if ( !is_null($sym) )
		{
			$symbol = $sym;
		}
		
		try
		{
			$old_debug = Configure::read('debug');
			
			Configure::write('debug', 0);
			Configure::write('Exception.handler', 'DocUtil::handleErrorUsage');
			$docx = new CreateDocxFromTemplate( $file );
			Configure::write('debug', $old_debug);
				
			$docx->setTemplateSymbol( $symbol );
						
		}
		catch( Exception $e )
		{
			CakeLog::error( 'DocUtil::open_template error: ' . $e->getMessage() . "\n\n" . $e->getTraceAsString() );
			
			//pr( $e->getMessage() );
			return false;
		}
		
		return $docx;
	}
	
	public function getTemplateVariables( CreateDocxFromTemplate $docx )
	{
		if ( !($docx instanceof CreateDocxFromTemplate) )
		{
			return false;
		}
		
		try 
		{			
			return $docx->getTemplateVariables();
		}
		catch( Exception $e )
		{
			return false;
		}
	}
	
	public function process_template( CreateDocxFromTemplate $docx, $textVariables, $htmlVariables = array() )
	{
		if ( !($docx instanceof CreateDocxFromTemplate) )
		{
			return false;
		}
		
		
				
		try 
		{
			//$docx = new CreateDocxFromTemplate( $file );
			
			//$docx->setTemplateSymbol('@');
			
				
			
			if ( is_array($textVariables) && !empty($textVariables) )
			{
				foreach( $textVariables as $var => $value )
				{
					//pr( $var );
					//pr( $value );
					
					if ( is_array($value) )
					{
						$docx->replaceTableVariable($value, array('parseLineBreaks' => true));
					}				
					else 
					{
						$docx->replaceVariableByText(array($var => $value), array('parseLineBreaks' => true));
							
						$docx->replaceVariableByText(array($var => $value), array('parseLineBreaks' => true, 'target' => 'header'));
							
						$docx->replaceVariableByText(array($var => $value), array('parseLineBreaks' => true, 'target' => 'footer'));
					}
				}
			}

			//set html options
			$html_options = array('isFile' => false, 'parseDivsAsPs' => false, 'downloadImages' => false, "strictWordStyles" => false);
				
			
			foreach( $htmlVariables as $token => $value )
			{
				//pr( $token );
				//pr( $value );
				
				//CakeLog::debug( 'token='.print_r($token, true) );
				//CakeLog::debug( 'token='.print_r($value, true) );
				
				$val = $this->process_html_token($docx, $token, $value);
				
				//pr( $val );
				
				//$val = $value;//'<div>'.$value.'</div>';
				
				//CakeLog::debug( 'val='.print_r($val, true) );
				
				if ( $val !== false )
				{				
					//force change utf-8 in html entities, because on one server it did not return corectly utf-8
					$val = mb_convert_encoding($val, 'HTML-ENTITIES', 'UTF-8');
					
					//$docx->replaceVariableByHTML( $token, 'block', $val, $html_options);
					$this->_replaceVariableByHTML( $docx, $token, 'block', $val, $html_options);
				}				
			}
			
			
		}
		catch( Exception $e )
		{				
			CakeLog::error( 'DocUtil::process_template error: ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n" );		
			return false;
		}
						
		return $docx;
				
	}
	
	private function _replaceVariableByHTML($docx, $var, $type = 'block', $html = '<html><body></body></html>', $options = array())
	{
		$old_debug = Configure::read('debug');
		Configure::write('debug', 0);
		
		if (isset($options['target'])) {
			$target = $options['target'];
		} else {
			$target = 'document';
		}
		if (isset($options['firstMatch'])) {
			$firstMatch = $options['firstMatch'];
		} else {
			$firstMatch = false;
		}
		$options['type'] = $type;
		$htmlFragment = new WordFragment($docx, $target);
		$htmlFragment->embedHTML($html, $options);
		
		$temp = $htmlFragment->__toString();
		
		//$temp = str_replace('<w:gridCol w:w="1"/>', '<w:gridCol/>', $temp);
		//$temp = str_replace('<w:gridCol w:w="1"/>', '<w:gridCol/>', $temp);
		
		$temp = str_replace('<w:tblCellSpacing w:w="30" w:type="dxa"', '<w:tblCellSpacing w:w="0" w:type="dxa"', $temp);
		
		//CakeLog::error( 'wordfragment='.print_r($temp, true) );
		
		unset( $htmlFragment );
		$htmlFragment = new WordFragment($docx, $target);
		$htmlFragment->addRawWordML($temp);
		
		
		$docx->replaceVariableByWordFragment(array($var => $htmlFragment), $options);
		
		Configure::write('debug', $old_debug);
	}
	
	public function lowlevel_convert_docx_to_pdf($output_file, $tmp_dir, $type )
	{
		$libreoffice_path = Configure::read('libreoffice_path');
		$openoffice_path = Configure::read('openoffice_path');
			
		$phpdocx_method = Configure::read('phpdocx_method');
		
		for( $i = 0; $i < 3; $i++ )
		{
			switch( $phpdocx_method )
			{
				case 'libreoffice':
					$rez = exec('HOME='. $tmp_dir .' '.$libreoffice_path.' --invisible --headless --convert-to pdf '. $output_file .'.docx --outdir ' . $tmp_dir, $output, $ret);
		
					//CakeLog::debug( print_r($ret, true) );
					//CakeLog::debug( print_r($rez, true) );
		
					break;
		
				case 'openoffice':
					$rez = exec('HOME='. $tmp_dir .' '.'java -jar ' . $openoffice_path . ' ' . $output_file . '.docx' . ' ' . $output_file . '.' . $type );
					break;
			}
							
			if ( file_exists( $output_file . '.pdf') )
			{
				return true;
			}
		}
		
		return false;
	}
	
	public function get_file( CreateDocxFromTemplate $docx, $output_file, $tmp_dir, $type = 'pdf' )
	{
		if ( $docx instanceof CreateDocxFromTemplate )
		{
			if ( file_exists( $output_file . '.docx') )
			{
				unlink($output_file . '.docx');
			}
			
			if ( file_exists( $output_file . '.pdf') )
			{
				unlink($output_file . '.pdf');
			}
			
			try
			{				
				$docx->createDocx( $output_file );
				
				if ( $type === 'docx' )
				{
					if ( file_exists( $output_file . '.docx') )
					{
						return true;
					}
					
					return false;
				}
				else
				{
					$docx->enableCompatibilityMode();
					
					$options = array(
						'debug' => true
					);
											
					if ( $this->lowlevel_convert_docx_to_pdf($output_file, $tmp_dir, $type) )
					{
						return true;
					}
									
				}
				
				CakeLog::error( 'File not generated.' );
				return false;					
			}
			catch( Exception $e )
			{
				Configure::write('debug', $old_debug);
				//pr( $e->getMessage());
				CakeLog::error( 'DocUtil::get_file: '.$e->getMessage() );
				return false;
			}
			
			return true;
		}
		else
		{
			CakeLog::error( 'DocUtil::get_file(): docx not CreateDocxFromTemplate . ' . Debugger::trace() );
			return false;
		}
		
		
		/*
		if ( $docx instanceof CreateDocxFromTemplate )
		{
			try 
			{
				$docx->createDocx( $output_file );
				
				//$docx->transformDocument( $output_file.'.docx', $output_file . '__lala.pdf');
				
				$rez = exec('HOME=/tmp/ /usr/bin/unoconv -f pdf ' . $output_file . '.docx', $output , $ret);
				
				if ($ret != 0)
				{					
					echo 'error'.var_dump($output).'<br /><br />';
					return false;
				}
				else
				{
					return true;
				}
			}
			catch( Exception $e )
			{
				return false;
			}
			
			return true;
		}
		else
		{
			return false;
		}
		*/
	}
	
		
	public static function process_html_token( CreateDocxFromTemplate $docx, $token, $html )
	{				
		if ( !($docx instanceof CreateDocxFromTemplate) )
		{
			return false;
		}
						
		$found_fonts_attrs = array();
		
		$dom = $docx->getDOMDocx();
		$docXPath = new DOMXPath($dom);
				
		//$search = $docx->getTemplateSymbol(). $token . $docx->getTemplateSymbol();
		$search = $token;
						
		$query = '//w:p/w:r[w:t[text()[contains(., "' . $search . '")]]]';
		
		//$query = '//w:p/w:r';
		
		$foundNodes = $docXPath->query($query);
		
		//pr( $foundNodes );
		
		foreach ($foundNodes as $node)
		{
			$nodeText = $node->ownerDocument->saveXML($node);			
			$cleanNodeText = strip_tags($nodeText);			
			if (strpos($cleanNodeText, $search) !== false || strpos($cleanNodeText, $token) !== false )
			{
								
				//prepare node token xml
				$docDOM_node = new DOMDocument();
				$docDOM_node->loadXML('<w:root xmlns:wpc="http://schemas.microsoft.com/office/word/2010/wordprocessingCanvas"
                                               xmlns:mo="http://schemas.microsoft.com/office/mac/office/2008/main"
                                               xmlns:mc="http://schemas.openxmlformats.org/markup-compatibility/2006"
                                               xmlns:mv="urn:schemas-microsoft-com:mac:vml"
                                               xmlns:o="urn:schemas-microsoft-com:office:office"
                                               xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"
                                               xmlns:m="http://schemas.openxmlformats.org/officeDocument/2006/math"
                                               xmlns:v="urn:schemas-microsoft-com:vml"
                                               xmlns:wp14="http://schemas.microsoft.com/office/word/2010/wordprocessingDrawing"
                                               xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"
                                               xmlns:w10="urn:schemas-microsoft-com:office:word"
                                               xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"
                                               xmlns:w14="http://schemas.microsoft.com/office/word/2010/wordml"
                                               xmlns:wpg="http://schemas.microsoft.com/office/word/2010/wordprocessingGroup"
                                               xmlns:wpi="http://schemas.microsoft.com/office/word/2010/wordprocessingInk"
                                               xmlns:wne="http://schemas.microsoft.com/office/word/2006/wordml"
                                               xmlns:wps="http://schemas.microsoft.com/office/word/2010/wordprocessingShape"
                                               mc:Ignorable="w14 wp14">' . $nodeText . '</w:root>');
				$docXpath_node = new DOMXPath($docDOM_node);
								
				//$ret = $docDOM_node->saveXML();
				
				//pr ( $ret );
				
				//get curent token block original font attributes
				$font_query = '//w:rFonts';
				$xmlfontNodesFont = $docXpath_node->query($font_query)->item(0);
				$font_allowed_attributes = array('ascii','hAnsi','cs');
				
				if($xmlfontNodesFont)
				{					
					foreach($xmlfontNodesFont->attributes as $attribute_name => $attribute_node)
					{
						$found_fonts_attrs[$token]['font_data'][$attribute_name] = $attribute_node->nodeValue;
						
						if(in_array($attribute_name, $font_allowed_attributes))
						{
							$found_fonts_attrs[$token]['font']['name'] = $attribute_node->nodeValue;
						}
					}
					
				}
				if (!isset($found_fonts_attrs[$token]['font']['name'])) {
					$found_fonts_attrs[$token]['font']['name'] = self::_default_font_family;
				}
				
				//get curent token block original font color
				$font_color_query = '//w:color';
				$xmlfontNodesColor = $docXpath_node->query($font_color_query)->item(0);
				
				//pr( $xmlfontNodesColor);
				
				if($xmlfontNodesColor)
				{
					foreach ($xmlfontNodesColor->attributes as $attribute_name => $attribute_node)
					{
						$found_fonts_attrs[$token]['font']['color'] = $attribute_node->nodeValue;
					}
				}
					
				//get curent token block original font decorations [bold]
				$font_bold_query = '//w:b';
				$xmlfontNodesBold = $docXpath_node->query($font_bold_query)->item(0);
												
				if($xmlfontNodesBold)
				{										
					//foreach ($xmlfontNodesBold->attributes as $attribute_name => $attribute_node) 
					//{						
						$found_fonts_attrs[$token]['font']['isbold'] = '1';
					//}
				}
					
				//get curent token block original font decorations [underline]
				$font_underline_query = '//w:u';
				$xmlfontNodesUnderline = $docXpath_node->query($font_underline_query)->item(0);
				
				//pr( $xmlfontNodesUnderline );
								
				if($xmlfontNodesUnderline)
				{
					foreach ($xmlfontNodesUnderline->attributes as $attribute_name => $attribute_node) 
					{
						$found_fonts_attrs[$token]['font']['isunderline'] = '1';
					}
				}
					
				//get curent token block original font decorations [italic]
				$font_italic_query = '//w:i';
				$xmlfontNodesItalic = $docXpath_node->query($font_italic_query)->item(0);
					
				//pr( $xmlfontNodesItalic);
				
				if($xmlfontNodesItalic)
				{
					//foreach ($xmlfontNodesItalic->attributes as $attribute_name => $attribute_node) 
					//{
						$found_fonts_attrs[$token]['font']['isitalic'] = '1';
					//}
				}
				
				//get curent token block original font size
				$font_size_query = '//w:sz';
				$xmlfontNodesSize = $docXpath_node->query($font_size_query)->item(0);

								
				if($xmlfontNodesSize)
				{
					foreach ($xmlfontNodesSize->attributes as $attribute_name => $attribute_node) 
					{
						//pr( $attribute_name );
						//pr( $attribute_node );
						
					   $found_fonts_attrs[$token]['font']['size'] = $attribute_node->nodeValue / 2;
					    
					}
				}
			}			
		}
		
			
		//pr( $found_fonts_attrs);
		
		$token_fonts = $found_fonts_attrs;
		$token_html = $token;
		
			$css_style = array();			
			if( isset($token_fonts[$token_html]['font']['name']) && strlen($token_fonts[$token_html]['font']['name']) > '0')
			{
				$css_style[] = 'font-family:' . $token_fonts[$token_html]['font']['name'];
			}
		
			if( isset($token_fonts[$token_html]['font']['size']) && strlen($token_fonts[$token_html]['font']['size']) > '0')
			{
				$css_style[] = 'font-size:' . $token_fonts[$token_html]['font']['size'] . 'pt';
				$css_style[] = 'line-height:' . $token_fonts[$token_html]['font']['size'] . 'pt';
			}
		
			if( isset($token_fonts[$token_html]['font']['color']) && strlen($token_fonts[$token_html]['font']['color']) > '0')
			{
				$css_style[] = 'color:#' . $token_fonts[$token_html]['font']['color'];
			}
		
			if( isset($token_fonts[$token_html]['font']['isbold']) && $token_fonts[$token_html]['font']['isbold'] == '1')
			{
				$css_style[] = 'font-weight:bold';
			}
		
			if( isset($token_fonts[$token_html]['font']['isitalic']) && $token_fonts[$token_html]['font']['isitalic'] == '1')
			{
				$css_style[] = 'font-style:italic';
			}
		
			if( isset($token_fonts[$token_html]['font']['isunderline']) && $token_fonts[$token_html]['font']['isunderline'] == "1")
			{
				$css_style[] = 'text-decoration:underline';
			}
		
			//dummy css control
			if(!empty($css_style))
			{
				$css_style[] = '';
			}
		
			$html = html_entity_decode('<div style="' . implode(';', $css_style) . '">' . $html . '</div>', ENT_COMPAT, 'UTF-8');

				
		return $html;
	}
	
	function test( $file )
	{
	
		
				
		//require_once '/var/www/phpdocx/phpdocx/classes/CreateDocx.inc';
		//require_once '/var/www/phpdocx/phpdocx/classes/CreateDocxFromTemplate.inc';
		
		//$docx = new CreateDocx();
		//$text = 'Lorem ipsum dolor sit amet. $VAR_DATE$ $VAR_NAME$ $VAR_TITLE$';
		//$docx->addText($text, $paramsText);
		//$docx->createDocx('template_test');
		
		
		$dir = '/var/www/phpdocx/';
		$template = 'test2';
		
		
		//$docx = new CreateDocxFromTemplate($dir. 'template_' . $template . '.docx');
		$docx = new CreateDocxFromTemplate( $file, array('preprocessed' => true) );
		
		$docx->importStyles( $file, 'replace');
		//$docx->importStyles($dir. 'template_' . $template . '.docx', 'replace');
		
				
		$test = $docx->getDOMDocx();
		$docXPath = new DOMXPath($test);
		
		
		
		$search = $docx->getTemplateSymbol(). 'TEST_1' . $docx->getTemplateSymbol();

		$query = '//w:t[text()[contains(., "' . $search . '")]]';
		
		/*
		if ($firstMatch) {
			$query = '(' . $query . ')[1]';
		}
		
		$foundNodes = $docXPath->query($query);
		*/
		
		
		//pr( $foundNodes );
		
		//pr($docx->getTemplateVariables());
		
		
		//$variables = $docx->getTemplateVariables();
		//$docx->processTemplate($variables);
		
		/*
		 $settings = array(
		 		'view' => 'outline',
		 		'zoom' => 70
		 );
		
		$docx->docxSettings($settings);
		
		$docx->enableCompatibilityMode();
		*/
		
		$data['fullName'] = 'test\ntest2';
		$data['title'] = array('titlu','asd');
		
		$variables = array(
				'VAR_DATE'  => date("n/d/Y H:i:s"),
				'VAR_NAME'  => $data['fullName'],
				'VAR_TITLE' => $data['title'],
				'INTRO'     => 'Onkologische Tagesklinik
Kloster Paradiese
Z.Hd. Dr. med. Th. Hamm
Im STiftsfeld 1
59494 Soest-Paradiese',
				'DATE' => date('d.m.Y H:i:s'),
				'GREETING' => 'Sehr geehrter Herr Kollege Dr. Hamm',
				'LETTER_TEXT2' => 'wir berichten Ihnen über Ihre Patientin Rohwetter, Christina, *15.12.1939, die sich heute in unserer
Palliativtagesklinik vorstellte.
Diagnose: MDS, Z.n. multiplen Bauch -OPs => chronische therapieresistente Schmerzen (R52.1)
Frau Rohwetter stellt sich mit intermittierenden Bauchschmerzen, konstant bei NRS 7/9-10 vor, die
Schmerzqualität beschreibt die Patientin als Druckschmerz, vom Unterbauch nach unten auf die Blase
drückend.
Es besteht Obstipationsneigung, auch intermittierend Übelkeit, jedoch kein Erbrechen. Auch
Mundtrockenheit und reduzierten Appetit werden von der Patientin beklagt. Bei der Sono ergibt sich kein
Aszites, Darmschlingen teilw. erweitert, Adhäsionen, V.a. Blasenentleeruungsstörungen => Restharn.
In einem ausführlichen Gespräch besprachen wir die Therapieplanung hinsichtlich Erhähung der Dosis des
transdermalen Fentanyls und Anpassung der Bedarfsmedikation, sowie Antiemetika und Laxantien. Wir
empfahlen auch die komplementären Maßnahmen und boten u.a. Durchführung der Rhythmischen
Einreibungen des Abdomens in unserer Tagesklinik.
Wir empfehlen, die Medikation wie folgt zu ergänzen bzw. anzupassen:
Durogesic Smat 37μg | alle 72h
Movicol | 1-0-1
Nystatin | 4xtgl.1Pip.
Paspertin | 3x10°
Bedarfsmedikation:
Paspertin° | 10°-20° bhei Übelkeit, Brechreiz
Laxoberal° | 5-8° bei Obstipation
Sevredol 10mg | 1/2 Tabl. bei Schmerzen
Abstral 100μg | 1Tabl.sublingual bei starken Schmerzattacken
Wir haben mit der Patientin einen Folgetermin in 2 Wochen vereinbart.
Wir danken für die freundliche Zuweisung des Patienten und verbleiben mit freundlichen Grüßen,
		
Dr. med. Boris Hait
Ltd. Oberarzt
Palliativzentrum am Katharinen-Hospital Unna',
				
				'LETTER_TEXT' => 'LETTER TEXT',
				'UN_TITLU' => 'titlul',
				'CEVA' => 'footeru'
		);
		
		//$footer_var = array('CEVA' => 'test');
		//$header_var = array('header' => array('UN_TITLU' => 'titlu'));
		//$header_var = array('UN_TITLU' => 'titlu');
		
		
		
		
		$docx->replaceVariableByText($variables, array('parseLineBreaks' => true));
		
		$docx->replaceVariableByText($variables, array('parseLineBreaks' => true, 'target' => 'header'));
		
		$docx->replaceVariableByText($variables, array('parseLineBreaks' => true, 'target' => 'footer'));
		
		//$docx->replaceVariableByHTML($var);
		
		//$wf = new WordFragment( $docx );
		
		$html = '<p style="color: transparent;font-size: 13pt;">test</p>';
		
		$wordML = '<w:p><w:r><w:t>A very simple paragraph with only text.</w:t></w:r></w:p>';
		
		try 
		{
			/*
			$docx->replaceVariableByWordML( array('TEST_1' => $wordML), array(
				'type' => 'inline',
				'firstMatch' => false,
				'target' => 'document'
			) );
			*/
			
			/*
			$docx->replaceVariableByHTML('TEST_1', 'inline', $html,  array(
				'parseDivsAsPs' => true,
				'parseLineBreaks' => true,
				'customListStyles' => true,
				'strictWordStyles' => false,
				'firstMatch' => false
					
			));
			*/

			/*
			$htmlDOCX = new HTML2WordML( $docx );
			$sFinalDocX = $htmlDOCX->render($html, array(
				'parseDivsAsPs' => true,
				'parseLineBreaks' => true,
				'customListStyles' => true,
				'strictWordStyles' => false,
				'firstMatch' => false
					
			));
			
			//pr( $sFinalDocX );
			
			 */
			
			/*
			$dompdf = new PARSERHTML();
        	$aTemp = $dompdf->getDompdfTree( $html, false, '*', false, '');
			*/
			
        	//pr( $aTemp );
        	
			//$wf->embedHTML( $html );
			
			//$docx->replaceVariableByWordFragment( array('TEST_1' => $wf) );
			
			/*
			$docx->replaceVariableByText(array('TEST_1' => '<p>test</p>'),  array(
					'parseDivsAsPs' => true,
					'parseLineBreaks' => true,
					'customListStyles' => true,
					'strictWordStyles' => false
			
			));
			*/
		}
		catch( Exception $e)
		{
			pr( $e->getMessage());
			pr( $e->getTraceAsString() );
		}
		
		
		//$docx->processTemplate( $variables );
		
		//$docx->addBreak(array('type' => 'page'));
		
		//$docx->createDocxAndDownload('/tmp/demo_template');
		$docx->createDocx( $dir . 'example_text2');
		
		//$docx->enableCompatibilityMode();
		
		try 
		{
			/*
			$docx->transformDocument( $dir . 'example_text2.docx', $dir . 'example_text2.html', '/tmp/', array(
				'debug' => true,
				'method' => 'script',
				'odfconverter' => false
			));
			*/
		}
		catch ( Exception $e)
		{
			pr( $e->getTraceAsString() );
		}
		
		$rez = exec('HOME=/tmp/ /usr/bin/unoconv -f pdf /var/www/phpdocx/example_text2.docx', $output , $ret);
		$rez = exec('HOME=/tmp/ /usr/bin/unoconv -f html /var/www/phpdocx/example_text2.docx', $output , $ret);
		
		if ($ret != 0)
		{
			echo 'error22'.var_dump($output).'<br /><br />';
		}
		else
		{
			echo 'success';
		}
		
	}
	
	public function preview_pdf( $file_info )
	{
				
		$files_helper = new FilesHelper();
		
		$file_id = $file_info['Files']['id'];
		
		$pdf_file = $files_helper->get_file_content_string( $file_id );
		
		if ( $pdf_file['extension'] != 'pdf' )
		{
			throw new Exception( __('Invalid file.') );
		}
		
		$img = new imagick();
		
		try
		{
			//$time_start = microtime();
				
			$img->setResolution(200,200);
				
			$img->readimageblob( $pdf_file['content'] );
				
			$num_pages = $img->getNumberImages();
				
			$i = 0;
			//for($i = 0;$i < $num_pages; $i++)
			//{
			$img->setIteratorIndex($i);
			$img->setImageFormat('jpeg');
			$img->setImageBackgroundColor('white');
		
								//$img->setImageAlphaChannel(Imagick::ALPHACHANNEL_DEACTIVATE);
								//$img->setImageAlphaChannel(11);
		
			$img->setimageopacity( 1 );
		
			$img->setimagecompression(Imagick::COMPRESSION_JPEG);
			$img->setimagecompressionquality(90);
		
			$img->scaleimage(0, 500);
		
			//$img->writeimage( $output_dir . DS . $i.'.jpg' );
			$jpg_file = $img->getimageblob();
			
			//}
									
			$img->destroy();
									
								//$time_end = microtime();
									
								//pr( 'took ' . ($time_end - $time_start));
		
			if ( !empty($jpg_file) )
			{
				return $jpg_file;
			}
			//if ( $num_pages > 0 )
			//{
				//return $num_pages;
			//}
			
			throw new Exception( __('No pages in pdf file.') );
		}
		catch (Exception $e)
		{
			CakeLog::error( 'DocUtil::preview_pdf error: ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n" );
				
			throw new Exception( __('Error opening file.') );
		}
		
	}
	
	public function _pdf_to_jpg( $file_info, $output_dir )
	{
		$files_helper = new FilesHelper();
		$indexfiles_lib = new IndexFilesLib();
		
		$file_id = $file_info['Files']['id'];
		
		
		//$pdf_file = $files_helper->get_file_content_string( $file_id );
		$extension = substr(strrchr($file_info['Files']['name'], "."), 1);
		$extension = strtolower($extension);
		
		
		if ( $extension != 'pdf' )
		{
			throw new Exception( __('Invalid file.') );
		}
		
		try 
		{
			$real_file = $indexfiles_lib->_return_real_file($file_info);
			
			//cd output
			if ( !chdir($output_dir) )
			{
				throw new Exception( 'DocUtil::_pdf_to_jpg error change dir to ' . $output_dir );
			}
			
// 			CakeLog::debug( 'DocUtil::_pdf_to_jpg start, file='. $file_id . ', output=' . $output_dir );

			//pdftoppm -q -jpeg -scale-to-x 900 ./19.pdf ""
			$pdftoppm_path = Configure::read('pdftoppm_path');
			
			$run = $pdftoppm_path . ' -q -jpeg '.$real_file.' p';
			$rez = exec( $run, $output , $ret);
			
			$indexfiles_lib->_cleanup_files();
			
			if ($ret != 0)
			{
				//Cache::delete('cron.indexing', 'misc_data_cache');
				throw new Exception( "Error converting ".$file_id." to jpg (\"$run\"), output: " . print_r($output, true) . ", exit code $ret\n" );
			}
			
			//CakeLog::debug( "$run" . ', pdftoppm ret=' . $ret . ',output=' . print_r($output, true) );
			
// 			CakeLog::debug( 'DocUtil::_pdf_to_jpg end, file='. $file_id );
			
			//find number of pages
			$num_pages = $files_helper->count_pdf_images( $file_info['Files']['sha1sum'] );
									
			if ( $num_pages > 0 )
			{
				return $num_pages;
			}
			
		} 
		catch (Exception $e) 
		{
			CakeLog::error( 'DocUtil::_pdf_to_jpg error: ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n" );
				
			throw new Exception( __('Error opening file.') );
		}
	}
	public function _old_pdf_to_jpg( $file_id, $output_dir)
	{
		$files_helper = new FilesHelper();
		
		CakeLog::debug( 'DocUtil::_pdf_to_jpg start, file='. $file_id );
		
		$pdf_file = $files_helper->get_file_content_string( $file_id );
		
		if ( $pdf_file['extension'] != 'pdf' )
		{
			throw new Exception( __('Invalid file.') );
		}
		
		$img = new imagick();
		
		try
		{
			//$time_start = microtime();
				
			$img->setResolution(200,200);
				
			$img->readimageblob( $pdf_file['content'] );
				
			$num_pages = $img->getNumberImages();
				
			for($i = 0;$i < $num_pages; $i++)
			{
				$img->setIteratorIndex($i);
				$img->setImageFormat('jpeg');
				$img->setImageBackgroundColor('white');
		
				$img->setimageopacity( 1 );
		
				$img->setimagecompression(Imagick::COMPRESSION_JPEG);
				$img->setimagecompressionquality(90);
		
				$img->scaleimage(900, 0);
		
				$img->writeimage( $output_dir . DS . $i.'.jpg' );
			}
									
			$img->destroy();
									
		
			CakeLog::debug( 'DocUtil::_pdf_to_jpg end, file='. $file_id . ', pages=' . $num_pages );
									
			if ( $num_pages > 0 )
			{
				return $num_pages;
			}
			
			throw new Exception( __('No pages in pdf file.') );
		}
		catch (Exception $e)
		{
			CakeLog::error( 'DocUtil::_pdf_to_jpg error: ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n" );
					
			throw new Exception( __('Error opening file.') );
		}
	}
	
	public function convert_pdf2jpg( $file_info )
	{
		set_time_limit(0);
		
		$files_helper = new FilesHelper();
		
		$file_id = $file_info['Files']['id'];

// 		pr( $file_info );
		
		if ( is_null($file_info['Files']['sha1sum']) )
		{
			$sha1sum = sha1( $pdf_file['content'] );
										
			if ( !$files_helper->save_file_checksum($file_info['Files']['id'], $sha1sum) )
			{
				CakeLog::error( 'DocUtil::convert_pdf2jpg error saving/updating sha1sum, options=' . print_r($file_info, true) . "\n" . 'sha1sum=' . $sha1sum . "\n\n" );
				
				throw new Exception( __('Error opening file.') );
			}

			$file_info['Files']['sha1sum'] = $sha1sum;
		}
		
		$files_helper->delete_old_pdf_images_cache();
		
		//pr( 'check cache' );
		
		if ( $files_helper->check_pdf_images_cache( $file_info['Files']['sha1sum'] ) )
		{
			//pr( 'GOT IT CACHED, USE IT');
			$cached_images = $files_helper->count_pdf_images( $file_info['Files']['sha1sum'] );
			
			if ( $cached_images > 0 )
			{
				return $cached_images;
			}
		}
		
		//pr( 'gen cache' );
		
		if ( !$files_helper->create_cache_pdf_images_dir( $file_info['Files']['sha1sum'] ) )
		{
			CakeLog::error( 'DocUtil::convert_pdf2jpg error creating cache pdf dir' );
			
			throw new Exception( __('Error opening file.') );
		}
		
		
		$output_dir = $files_helper->get_cache_pdf_images_dir( $file_info['Files']['sha1sum'] );
		
		//pr( $output_dir );
		
		try 
		{
			//$num_pages = $this->_old_pdf_to_jpg($file_id, $output_dir);
			
			$num_pages = $this->_pdf_to_jpg( $file_info, $output_dir );
			
			return $num_pages;
		}
		catch (Exception $e)
		{
			CakeLog::error( 'DocUtil::convert_pdf2jpg error: ' . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n\n" );
			
			throw new Exception( __('Error opening file.') );
		}
		
	}
}
?>
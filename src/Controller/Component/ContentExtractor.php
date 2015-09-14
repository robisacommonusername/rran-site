<?php
	class ContentExtractor {
		public $supported_types = [
			'text/plain',
			'text/html',
			'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
		];
		
		public function __construct($type){
			$this->type = $type;
		}
		
		public function supported(){
			return in_array($this->type, $this->supported_types);
		}
		
		public function extract($fn){
			if (!$this->supported()){
				throw new Exception("Unsupported file type, cannot extract content");
			}
			
			switch ($this->type){
				case 'text/plain':
				return $this->_extract_text($fn);
				
				case 'text/html':
				return $this->_extract_html($fn);
				
				case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
				return $this->_extract_docx($fn);
				
				default:
				throw new Exception("Extractor for this filetype has not been implemented");
			}
			
			return '';
		}
		
		private function _extract_docx($fn){
			$ret = '';
			// Create new ZIP archive
			$zip = new ZipArchive;
			
			// Open received archive file
			if (true === $zip->open($fn)) {
			    // If done, search for the data file in the archive
			    if (($index = $zip->locateName('word/document.xml')) !== false) {
			        // If found, read it to the string
			        $data = $zip->getFromIndex($index);
			        // Close archive file
			        // Load XML from a string
			        // Skip errors and warnings
			        $xml = new DOMDocument();
					$xml->loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
			        // Return data without XML formatting tags
			        $ret = strip_tags($xml->saveXML());
			    }
			    $zip->close();
			}
			
			// In case of failure return empty string
			return $ret;
		}
		
		private function _extract_text($fn){
			return file_get_contents($fn);
		}
		
		private function _extract_html($fn){
			$contents = file_get_contents($fn);
			return strip_tags($contents);
		}
	}
?>

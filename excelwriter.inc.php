<?php

/*

	this is a modified version of excelwriter.inc.php
	original version get from: http://phpsoft.mirrors.phpclasses.org/browse/package/2037.html

	original version is made by:
	###############################################
	####                                       ####
	####    Author : Harish Chauhan            ####
	####    Date   : 31 Dec,2004               ####
	####    Updated:                           ####
	####                                       ####
	###############################################

*/

	/*
	 * Class is used for save the data into microsoft excel format. It takes data into array or you can write data column vise.
	 */
	Class ExcelWriter{
		var $fp=null;
		var $error;
		var $state="CLOSED";
		var $newRow=false;
		var $nome;
		function ExcelWriter($file="") {
			$this->nome=$file;
			return $this->open($file);
		}

// 		* 			if you are using file name with directory i.e. test/myFile.xls then the directory must be existed on the system and have permissioned properly to write the file.

		function open($file) {
			if($this->state!="CLOSED") {
				$this->error="Error : Another file is opend .Close it to save the file";
				return false;
			}

			if(!empty($file))
				$this->fp=@fopen($file,"w+");
			else{
				$this->error="Usage : New ExcelWriter('fileName')";
				return false;
			}
			if($this->fp==false) {
				$this->error="Error: Unable to open/create File.You may not have permmsion to write the file.";
				return false;
			}
			$this->state="OPENED";
			fwrite($this->fp,$this->GetHeader());
			return $this->fp;
		}

		function close() {
			if($this->state!="OPENED") {
				$this->error="Error : Please open the file.";
				return false;
			}
			if($this->newRow) {
				fwrite($this->fp,"</tr>");
				$this->newRow=false;
			}

			fwrite($this->fp,$this->GetFooter());
			fclose($this->fp);
			$this->state="CLOSED";
			return ;
		}

		/* @Params : Void
		 *  @return : Void
		 * This function write the header of Excel file.
		*/
		function GetHeader() {
			$header = <<<EOH
				<html xmlns:o="urn:schemas-microsoft-com:office:office"
				xmlns:x="urn:schemas-microsoft-com:office:excel"
				xmlns="http://www.w3.org/TR/REC-html40">

				<head>
				<meta name=ProgId content=Excel.Sheet>
				<!--[if gte mso 9]><xml>
				<o:DocumentProperties>
				<o:LastAuthor>Sriram</o:LastAuthor>
				<o:LastSaved>2005-01-02T07:46:23Z</o:LastSaved>
				<o:Version>10.2625</o:Version>
				</o:DocumentProperties>
				<o:OfficeDocumentSettings>
				<o:DownloadComponents/>
				</o:OfficeDocumentSettings>
				</xml><![endif]-->
				<style>
				table
					{border:.5pt solid windowtext;
					mso-displayed-decimal-separator:"\.";
					mso-displayed-thousand-separator:"\,";}
				@page
					{margin:1.0in .75in 1.0in .75in;
					mso-header-margin:.5in;
					mso-footer-margin:.5in;}
				tr
					{mso-height-source:auto;}
				col
					{mso-width-source:auto;}
				br
					{mso-data-placement:same-cell;}
				.style0
					{mso-number-format:General;
					text-align:general;
					vertical-align:bottom;
					white-space:normal;
					mso-rotate:0;
					mso-background-source:auto;
					mso-pattern:auto;
					border:.5pt solid windowtext;
					color:windowtext;
					font-size:10.0pt;
					font-weight:400;
					font-style:normal;
					text-decoration:none;
					font-family:Arial;
					mso-generic-font-family:auto;
					mso-font-charset:0;
					border:none;
					mso-protection:locked visible;
					mso-style-name:Normal;
					mso-style-id:0;}
				td
					{mso-style-parent:style0;
					padding-top:1px;
					padding-right:1px;
					padding-left:1px;
					mso-ignore:padding;
					color:windowtext;
					font-size:10.0pt;
					font-weight:400;
					font-style:normal;
					text-decoration:none;
					font-family:Arial;
					mso-generic-font-family:auto;
					mso-font-charset:0;
					mso-number-format:General;
					text-align:general;
					vertical-align:bottom;
					border:none;
					mso-background-source:auto;
					mso-pattern:auto;
					mso-protection:locked visible;
					white-space:normal;
					mso-rotate:0;}
				.xl24
					{mso-style-parent:style0;
					white-space:normal;
					border:.5pt solid windowtext;}
				.xl25
					{
					mso-height-source:userset;
					height:38.25pt';
					color:white;
					font-size:10.0pt;
					font-weight:400;
					font-style:normal;
					text-decoration:none;
					font-family:Arial, sans-serif;
					mso-font-charset:0;
					mso-number-format:General;
					text-align:center;
					vertical-align:middle;
					border:.5pt solid windowtext;
					background:#ffaa00;
					mso-pattern:auto none;
					white-space:normal;}
				.xl26
					{
					mso-height-source:userset;
					height:38.25pt';
					color:red;
					font-size:14.0pt;
					font-weight:bold;
					font-style:normal;
					text-decoration:none;
					font-family:Arial, sans-serif;
					mso-font-charset:0;
					mso-number-format:General;
					text-align:center;
					vertical-align:middle;
					border:.5pt solid windowtext;
					background:white;
					mso-pattern:auto none;
					white-space:normal;}
				.xl27
					{
					mso-height-source:userset;
					height:38.25pt';
					color:black;
					font-size:8.0pt;
					font-weight:400;
					font-style:normal;
					text-decoration:none;
					font-family:Arial, sans-serif;
					mso-font-charset:0;
					mso-number-format:General;
					text-align:center;
					vertical-align:middle;
					border:.5pt solid windowtext;
					background:#CCCCCC;
					mso-pattern:auto none;
					white-space:normal;}
				</style>
				<!--[if gte mso 9]><xml>
				<x:ExcelWorkbook>
					<x:ExcelWorksheets>
					<x:ExcelWorksheet>
					<x:Name>srirmam</x:Name>
					<x:WorksheetOptions>
						<x:Selected/>
						<x:ProtectContents>False</x:ProtectContents>
						<x:ProtectObjects>False</x:ProtectObjects>
						<x:ProtectScenarios>False</x:ProtectScenarios>
					</x:WorksheetOptions>
					</x:ExcelWorksheet>
					</x:ExcelWorksheets>
					<x:WindowHeight>10005</x:WindowHeight>
					<x:WindowWidth>10005</x:WindowWidth>
					<x:WindowTopX>120</x:WindowTopX>
					<x:WindowTopY>135</x:WindowTopY>
					<x:ProtectStructure>False</x:ProtectStructure>
					<x:ProtectWindows>False</x:ProtectWindows>
				</x:ExcelWorkbook>
				</xml><![endif]-->
				</head>

				<body link=blue vlink=purple>
				<table x:str border=0 cellpadding=0 cellspacing=0 style='border-collapse: collapse;table-layout:fixed;'>
EOH;
				header('Cache-Control: maxage=3600');
				header('Pragma: public');
				header ("Content-Type: application/vnd.ms-excel");
				header("Content-Disposition: inline; filename=$this->nome");
			return $header;
		}

		function GetFooter() {
			return "</table></body></html>";
		}

		function writeLine($line_arr) {
			if($this->state!="OPENED") {
				$this->error="Error : Please open the file.";
				return false;
			}
			if(!is_array($line_arr)) {
				$this->error="Error : Argument is not valid. Supply an valid Array.";
				return false;
			}
			fwrite($this->fp,"<tr>");
			foreach($line_arr as $col)
				fwrite($this->fp,"<td class=xl24 width=64 >$col</td>");
			fwrite($this->fp,"</tr>");
		}

		function writeLine_titolo($line_arr) {
			if($this->state!="OPENED") {
				$this->error="Error : Please open the file.";
				return false;
			}
			if(!is_array($line_arr)) {
				$this->error="Error : Argument is not valid. Supply an valid Array.";
				return false;
			}
			fwrite($this->fp,"<tr>");
			foreach($line_arr as $col)
				fwrite($this->fp,"<td class=xl26 width=64 height=300 colspan=12>$col</td>");
			fwrite($this->fp,"</tr>");
		}

		function writeLine_orange($line_arr) {
			if($this->state!="OPENED") {
				$this->error="Error : Please open the file.";
				return false;
			}
			if(!is_array($line_arr)) {
				$this->error="Error : Argument is not valid. Supply an valid Array.";
				return false;
			}
			fwrite($this->fp,"<tr>");
			foreach($line_arr as $col)
				fwrite($this->fp,"<td class=xl25>$col</td>");
			fwrite($this->fp,"</tr>");
		}

		function writeRow() {
			if($this->state!="OPENED") {
				$this->error="Error : Please open the file.";
				return false;
			}
			if($this->newRow==false)
				fwrite($this->fp,"<tr>");
			else
				fwrite($this->fp,"</tr><tr>");
			$this->newRow=true;
		}

		function writeRow_orange() {
			if($this->state!="OPENED") {
				$this->error="Error : Please open the file.";
				return false;
			}
			if($this->newRow==false)
				fwrite($this->fp,"<tr>");
			else
				fwrite($this->fp,"</tr><tr>");
			$this->newRow=true;
		}

		function writeCol($value) {
			if($this->state!="OPENED") {
				$this->error="Error : Please open the file.";
				return false;
			}
			fwrite($this->fp,"<td class=xl24 width=64 >$value</td>");
		}
		function writeCol_p0($value) {
			if($this->state!="OPENED") {
				$this->error="Error : Please open the file.";
				return false;
			}
			fwrite($this->fp,"<td class=xl24 width=58>$value</td>");
		}
		function writeTableSis() {
			if($this->state!="OPENED") {
				$this->error="Error : Please open the file.";
				return false;
			}
// 			if($this->newRow==false)
				fwrite($this->fp,"<td class=xl24><table>");
// 			else
// 				fwrite($this->fp,"</tr><tr>");
// 			$this->newRow=true;
		}
		function closeTableSis() {
			if($this->state!="OPENED") {
				$this->error="Error : Please open the file.";
				return false;
			}
// 			if($this->newRow) {
				fwrite($this->fp,"</tr></table></td>");
// 				$this->newRow=false;
// 			}
		}
		function writeTitolo0_av($line_arr) {
			if($this->state!="OPENED") {
				$this->error="Error : Please open the file.";
				return false;
			}
			if(!is_array($line_arr)) {
				$this->error="Error : Argument is not valid. Supply an valid Array.";
				return false;
			}
			foreach($line_arr as $col)
				fwrite($this->fp,"<td class=xl27>$col</td>");
// 			fwrite($this->fp,"</tr>");
		}

		function writeTitolo1_av($line_arr) {
			if($this->state!="OPENED") {
				$this->error="Error : Please open the file.";
				return false;
			}
			if(!is_array($line_arr)) {
				$this->error="Error : Argument is not valid. Supply an valid Array.";
				return false;
			}
// 			fwrite($this->fp,"<tr>");
			foreach($line_arr as $col)
				fwrite($this->fp,"<td class=xl27>$col</td>");
// 			fwrite($this->fp,"</tr>");
		}
		function writeTitolo2_av($line_arr) {
			if($this->state!="OPENED") {
				$this->error="Error : Please open the file.";
				return false;
			}
			if(!is_array($line_arr)) {
				$this->error="Error : Argument is not valid. Supply an valid Array.";
				return false;
			}
			fwrite($this->fp,"<td class=xl27><table><tr>");
			foreach($line_arr as $col)
				fwrite($this->fp,"<td class=xl27>$col</td>");
			fwrite($this->fp,"</tr></table></td>");
		}
	}
?>

<?php

	function xml2array($contents, $get_attributes=1, $priority = 'tag') {
		if(!$contents) return array();

		if(!function_exists('xml_parser_create')) {
			//print "'xml_parser_create()' function not found!";
			return array();
		}

		//Get the XML parser of PHP - PHP must have this module for the parser to work
		$parser = xml_parser_create('');
		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, trim($contents), $xml_values);
		xml_parser_free($parser);

		if(!$xml_values) return;//Hmm...

		//Initializations
		$xml_array = array();
		$parents = array();
		$opened_tags = array();
		$arr = array();

		$current = &$xml_array; //Refference

		//Go through the tags.
		$repeated_tag_index = array();//Multiple tags with same name will be turned into an array
		foreach($xml_values as $data) {
			unset($attributes,$value);//Remove existing values, or there will be trouble

			//This command will extract these variables into the foreach scope
			// tag(string), type(string), level(int), attributes(array).
			extract($data);//We could use the array by itself, but this cooler.

			$result = array();
			$attributes_data = array();
			
			if(isset($value)) {
				if($priority == 'tag') $result = $value;
				else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
			}

			//Set the attributes too.
			if(isset($attributes) and $get_attributes) {
				foreach($attributes as $attr => $val) {
					if($priority == 'tag') $attributes_data[$attr] = $val;
					else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
				}
			}

			//See tag status and do the needed.
			if($type == "open") {//The starting of the tag '<tag>'
				$parent[$level-1] = &$current;
				if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
					$current[$tag] = $result;
					if($attributes_data) $current[$tag. '_attr'] = $attributes_data;
					$repeated_tag_index[$tag.'_'.$level] = 1;

					$current = &$current[$tag];

				} else { //There was another element with the same tag name

					if(isset($current[$tag][0])) {//If there is a 0th element it is already an array
						$current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
						$repeated_tag_index[$tag.'_'.$level]++;
					} else {//This section will make the value an array if multiple tags with the same name appear together
						$current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array
						$repeated_tag_index[$tag.'_'.$level] = 2;
						
						if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
							$current[$tag]['0_attr'] = $current[$tag.'_attr'];
							unset($current[$tag.'_attr']);
						}

					}
					$last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
					$current = &$current[$tag][$last_item_index];
				}

			} elseif($type == "complete") { //Tags that ends in 1 line '<tag />'
				//See if the key is already taken.
				if(!isset($current[$tag])) { //New Key
					$current[$tag] = $result;
					$repeated_tag_index[$tag.'_'.$level] = 1;
					if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;

				} else { //If taken, put all things inside a list(array)
					if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array...

						// ...push the new element into that array.
						$current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
						
						if($priority == 'tag' and $get_attributes and $attributes_data) {
							$current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
						}
						$repeated_tag_index[$tag.'_'.$level]++;

					} else { //If it is not an array...
						$current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
						$repeated_tag_index[$tag.'_'.$level] = 1;
						if($priority == 'tag' and $get_attributes) {
							if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
								
								$current[$tag]['0_attr'] = $current[$tag.'_attr'];
								unset($current[$tag.'_attr']);
							}
							
							if($attributes_data) {
								$current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
							}
						}
						$repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken
					}
				}

			} elseif($type == 'close') { //End of tag '</tag>'
				$current = &$parent[$level-1];
			}
		}
		
		return($xml_array);
	}
	function getcontents($url){
	
		 
			   $curl_handle=curl_init();
			  curl_setopt($curl_handle,CURLOPT_URL,$url);
			  curl_setopt($curl_handle,CURLOPT_CONNECTTIMEOUT,2);
			  curl_setopt($curl_handle,CURLOPT_RETURNTRANSFER,1);
			  $result = curl_exec($curl_handle);
			  curl_close($curl_handle); 
			  
			return $result;  
	}
	function getcompanycontact($name,$id){
			$name=trim($name);
			$xml='<?xml version="1.0" encoding="utf-8"?>
<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
<soap12:Body>
<GetClientContacts  xmlns="http://filetrac.onlinereportinginc.com/ftservices/">
<oParms>
<Login>XXX</Login>
<Password>XXX</Password>
<CompanyKey>XXX</CompanyKey>
<ClientCompanyName>'.$name.'</ClientCompanyName>
<ClientCompanyID>'.$id.'</ClientCompanyID>
<IncludeInactives>false</IncludeInactives>
</oParms>
</GetClientContacts>
</soap12:Body>
</soap12:Envelope>';
			 $sml=strlen($xml);
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => "https://ftservices.onlinereportinginc.com/service.asmx?op=GetClientContacts",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS =>$xml,
			  CURLOPT_HTTPHEADER => array(
				"Accept: */*",
				"Accept-Encoding: gzip, deflate",
				"Cache-Control: no-cache",
				"Connection: keep-alive",
				"Content-Length: ".$sml,
				"Content-Type: application/soap+xml",
				"Host: ftservices.onlinereportinginc.com",
				"cache-control: no-cache"
			  ),
			));

			$response = curl_exec($curl);
			curl_close($curl);
			return $array=xml2array($response );
	}
	function getcompany($name){
			$name=trim($name);
			$xml='<?xml version="1.0" encoding="utf-8"?>
<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
<soap12:Body>
<GetClientCompanies xmlns="http://filetrac.onlinereportinginc.com/ftservices/">
<oParms>
<Login>XXX</Login>
<Password>XXX</Password>
<CompanyKey>XXX</CompanyKey>
<CompanyName>'.$name.'</CompanyName>
<IncludeInactives>false</IncludeInactives>
</oParms>
</GetClientCompanies>
</soap12:Body>
</soap12:Envelope>';
			 $sml=strlen($xml);
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => "https://ftservices.onlinereportinginc.com/service.asmx?op=GetClientCompanies",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS =>$xml,
			  CURLOPT_HTTPHEADER => array(
				"Accept: */*",
				"Accept-Encoding: gzip, deflate",
				"Cache-Control: no-cache",
				"Connection: keep-alive",
				"Content-Length: ".$sml,
				"Content-Type: application/soap+xml",
				"Host: ftservices.onlinereportinginc.com",
				"cache-control: no-cache"
			  ),
			));

			$response = curl_exec($curl);
			curl_close($curl);
			return $array=xml2array($response );
	}
	
	function getcompanycontactbyid($id){
			$name=trim($name);
			$xml='<?xml version="1.0" encoding="utf-8"?>
			<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
			<soap12:Body>
			<GetUsers xmlns="http://filetrac.onlinereportinginc.com/ftservices/">
			<oParms>
			<Login>XXX</Login>
			<Password>XXX</Password>
			<CompanyKey>XXX</CompanyKey>
			<ClientCompanyID>'.$id.'</ClientCompanyID>
			<IncludeInactives>false</IncludeInactives>
			</oParms>
			</GetUsers>
			</soap12:Body>
			</soap12:Envelope>';
			 $sml=strlen($xml);
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => "https://ftservices.onlinereportinginc.com/service.asmx?op=GetUsers",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS =>$xml,
			  CURLOPT_HTTPHEADER => array(
				"Accept: */*",
				"Accept-Encoding: gzip, deflate",
				"Cache-Control: no-cache",
				"Connection: keep-alive",
				"Content-Length: ".$sml,
				"Content-Type: application/soap+xml",
				"Host: ftservices.onlinereportinginc.com",
				"cache-control: no-cache"
			  ),
			));

			$response = curl_exec($curl);
			curl_close($curl);
			return $array=xml2array($response );
	}
	function AddClaimNote($string,$id){
	
			$string	= addslashes($string);

			$xml='<?xml version="1.0" encoding="utf-8"?>
			<soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope">
			<soap12:Body>
			<AddClaimNote  xmlns="http://filetrac.onlinereportinginc.com/ftservices/">
			<oNote>
			<Login>XXX</Login>
			<Password>XXX</Password>
			<CompanyKey>XXX</CompanyKey>
			<FileTracClaimID>'.$id.'</FileTracClaimID>
			<Note>'.$string.'</Note>
			<IncludeInactives>false</IncludeInactives>
			</oNote>
			</AddClaimNote>
			</soap12:Body>
			</soap12:Envelope>';
			$len=strlen($xml);
			$ch = curl_init(); 
			curl_setopt($ch, CURLOPT_URL,'https://ftservices.onlinereportinginc.com/service.asmx?op=AddClaimNote');
			curl_setopt($ch, CURLOPT_POST, 1);

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Length: ".$len,
			"Content-type: text/xml;charset=utf-8",
			"Host: ftservices.onlinereportinginc.com" 
			));
				  
			$data = curl_exec($ch); 
			$results=xml2array($data);
			$info = curl_getinfo($ch);
			curl_close($ch);
		 
	
	}
	
	function onlinereportinginc( $atts ) { 
		
	
	      $user_id	=	get_current_user_id();
			$company_id		=	$saved=get_user_meta( $user_id,  'company_id');
			$contacts_id	=	$contact=get_user_meta( $user_id,  'contacts_id');
		 
			$successmsg='';			
 
			if(isset($_POST['client_claim'])){
			
				if(trim($_POST['client_claim']) != ""){
					$CompanyName		=	$_POST['client_compnay_name'];
					$ClientContactName	=	$_POST['client_contact_name'];
					$ClientClaimNum		=	$_POST['client_claim'];
					$ClaimDateReceived	=	date('Y-m-d');
					$ContactFirstName	=	$_POST['first_name'];
					$ContactLastName	=	$_POST['lastname'];
					$policy_num			=	$_POST['policy_num'];
					$phone_number		=	$_POST['phone_number'];
					$email				=	$_POST['email'];
					$address			=	$_POST['address'];
					$instructions			=	addslashes($_POST['instructions']);
					$description			=	addslashes($_POST['description']);
					$arr=explode(" ",$ClientContactName);
					$CompanyName=urlencode($CompanyName);
					$ClientCompanyID	=	$company_id[0];
					$contactid	=	$contacts_id[0];
					 
				 /* echo '<pre>';
				  print_R($arr);
				  echo "</pre>";*/
				// print_r($contactid);
				// print_R( $CompanyName);
		 
			      	  $xml='<?xml version="1.0" encoding="utf-8"?>
						<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
							<soap:Body>
								<AddClaim xmlns="http://filetrac.onlinereportinginc.com/ftservices/">
									<oClaim>
										<Login>XXX</Login>
										<Password>XXX</Password>
										<CompanyKey>XXX</CompanyKey>
										<IncludeInactives>false</IncludeInactives>									
										<ClientCompanyID>'.$ClientCompanyID.'</ClientCompanyID>	
										<ClientContactID>'.$contactid.'</ClientContactID>
										<ClientClaimNum>'.$ClientClaimNum.'</ClientClaimNum>
										<ClientContactName>'.$ClientContactName.'</ClientContactName>
										<PolicyNumber>'.$policy_num.'</PolicyNumber>
										<ClaimDateReceived>'.$ClaimDateReceived.'</ClaimDateReceived>
										<ClaimantFName>'.$ContactFirstName.'</ClaimantFName>
										<ClaimantLName>'.$ContactLastName.'</ClaimantLName>
										<ClaimantEMail>'.$email.'</ClaimantEMail>
										<ClaimantPhone1>'.$phone_number.'</ClaimantPhone1>
										<ClaimantAddr1>'.$address.'</ClaimantAddr1>
										<InsuredFName>'.$ContactFirstName.'</InsuredFName>
										<InsuredLName>'.$ContactLastName.'</InsuredLName>
										<LossDescription>'.$description.'</LossDescription>
										<SpecialInstructions>'.$instructions.'</SpecialInstructions>
										<Contacts>							
											<Contacts>	
												<ContactType>Insured</ContactType>
												<ContactIsPrimary>1</ContactIsPrimary>												
												<ContactFirstName>'.$ContactFirstName.'</ContactFirstName>
												<ContactLastName>'.$ContactLastName.'</ContactLastName>
												<ContactEmail>'.$email.'</ContactEmail>
												<ContactAddress1>'.$address.'</ContactAddress1>
												<ContactCellPhone>'.$phone_number.'</ContactCellPhone>
											</Contacts>
										</Contacts>
									</oClaim>
								</AddClaim>
							</soap:Body>
						</soap:Envelope>';
						
					 
				$len=strlen($xml);
				  
				$ch = curl_init(); 
				curl_setopt($ch, CURLOPT_URL,'https://ftservices.onlinereportinginc.com/service.asmx?op=AddClaim');
				curl_setopt($ch, CURLOPT_POST, 1);

				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
				curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				"Content-Length: ".$len,
				"Content-type: text/xml;charset=utf-8",
				"Host: ftservices.onlinereportinginc.com" 
				));
				 
				 
				$data = curl_exec($ch); 
				$results=xml2array($data);
				$info = curl_getinfo($ch);
				curl_close($ch);
				 

				// echo $results['soap:Envelope']['soap:Body']['AddClaimResponse']['AddClaimResult'];
				$erromsg="";
				$successmsg="Claim submission successful. Your case number is #".$results['soap:Envelope']['soap:Body']['AddClaimResponse']['AddClaimResult'];
				if($results['soap:Envelope']['soap:Body']['AddClaimResponse']['AddClaimResult'] == ""){
					$successmsg="";
					$erromsg="Faild :Please contact to Administrator";
					
				}
				$claimid=$results['soap:Envelope']['soap:Body']['AddClaimResponse']['AddClaimResult'];
				if(trim($_POST['ClaimNote']) != ""){
				
					$notes=$_POST['ClaimNote'];
					AddClaimNote($_POST['ClaimNote'],$claimid);
				}
					if(isset($_POST['files'])){
					
						if(count($_POST['files']) > 0){
						
							for($i = 0 ; $i < count($_POST['files']);$i++){
						 
								$filedata= getcontents('https://acsi-incorporated.com/uploads/'.$_POST['files'][$i]); 
							 	$data = base64_encode($filedata); 
							
							
								$xml='<?xml version="1.0" encoding="utf-8"?>
								<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
								<soap:Body>
								<UploadReport xmlns="http://filetrac.onlinereportinginc.com/ftservices/">
								  <oReport>
								   <Login>XXX</Login>
								   <Password>XXX</Password>
								   <CompanyKey>XXX</CompanyKey>
								    <ReportFileName>'.$_POST['files'][$i].'</ReportFileName>
									 <ReportTitle>'.$_POST['files'][$i].'</ReportTitle>
									<ReportFile>'.$data.'</ReportFile>
									<ClaimID>'.$results['soap:Envelope']['soap:Body']['AddClaimResponse']['AddClaimResult'].'</ClaimID>
								  </oReport>
								</UploadReport>
								</soap:Body>
								</soap:Envelope>';
							 
								$len=strlen($xml);
								$ch = curl_init(); 
								curl_setopt($ch, CURLOPT_URL,'https://ftservices.onlinereportinginc.com/service.asmx?op=UploadReport');

								curl_setopt($ch, CURLOPT_POST, 1);

								curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
								curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
								curl_setopt($ch, CURLOPT_HTTPHEADER, array(
								"Content-Length: ".$len,
							 	"Content-type: text/xml;charset=utf-8",
								"Host: ftservices.onlinereportinginc.com" 
								));
								
								$data1 = curl_exec($ch); 
							 	$results1=xml2array($data1);
								 
								curl_close($ch);
								 sleep(7);
								 
								 }
						}
					}
					//}
				}
			}
	 
		?> 
<style>
				 
form {
  max-width: 300px;
  margin: 10px auto;
  padding: 10px 20px;
 
}
.mk{
  max-width: 300px;
  margin: 10px auto;
  padding: 10px 20px;
 
}
pre{display:none}
    
		</style>
     

      <form method="post" id="forms" name="forms">
       <div style="color:green;font-weight:bold"><?php echo $successmsg ;?></div>
	   <div style="color:red;font-weight:bold"> <?php echo $erromsg?></div>
        <fieldset> 
			<label class="lab">Client claim #</label> 
			<input type="text" id="name" name="client_claim" placeholder="Client Claim #">
			<label class="lab">Insured First name</label> 
			<input type="text" id="name" name="first_name" placeholder="Insured First name">
			<label class="lab">Insured Last name</label> 
			<input type="text" id="name" name="lastname" placeholder="Insured Last name">
			<label class="lab">Policy #</label> 
			<input type="text" id="name" name="policy_num" placeholder="Policy #">
			<label class="lab">Special Instructions</label> 
 
			<textarea rows="4" cols="50"   name="instructions" placeholder="Special Instructions Here"></textarea>
			
			<label class="lab">Loss Description</label> 
 
			<textarea rows="4" cols="50"   name="description" placeholder="Loss Description Here"></textarea>
       
			</fieldset>
			<div id='loaded' style='display:none'>
		</div>
        
       <div id='loaded'>
		</div>
      
      </form>
     
	<form action="https://acsi-incorporated.com/upload.php" class="dropzone"></form>
	<div style="clear:both"></div>
	 
	<div class="mk"> 
		<button type="submit"   onclick="document.forms.submit()">Make a Claim</button>
	</fieldset> 
	</div>
	<div style="clear:both"></div>
	<script src="https://acsi-incorporated.com/wp-content/plugins/feed-plugin/dropzone.js"></script>
	<link rel="stylesheet" href="https://rawgit.com/enyo/dropzone/master/dist/dropzone.css">
		<?php
	} 
	add_shortcode( 'onlinereportinginc_api', 'onlinereportinginc' );
	 
 
add_action( 'edit_user_profile', 'crf_show_extra_profile_fields' );

function crf_show_extra_profile_fields( $user ) {

	$array=getcompany('');
	$saved=get_user_meta( $user->ID,  'company_id');
	$contact=get_user_meta( $user->ID,  'contacts_id');
	
	?>
	<h3><?php esc_html_e( 'Personal Information', 'crf' ); ?></h3>
	<script>
		function loadDoc(id,contactid) {
		  var xhttp = new XMLHttpRequest();
		  xhttp.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
			 document.getElementById("demo").innerHTML = this.responseText;
			}
		  };
	
		  xhttp.open("GET", "https://acsi-incorporated.com/getcontacts.php?id="+id+'&contactid='+contactid, true);
		  xhttp.send();
		}
	</script>
	<table class="form-table">
		<tr>
			<th><label for="year_of_birth">Company List</label></th>
			<td>
				<select name="company_id" onchange="loadDoc(this.value,'')">
					<option>Select</option>
					<?php
						for($i = 0 ; $i < count($array['soap:Envelope']['soap:Body']['GetClientCompaniesResponse']['GetClientCompaniesResult']['ClientCompanyExtract']) ; $i++){
								$elected='';
								if($saved[0] == $array['soap:Envelope']['soap:Body']['GetClientCompaniesResponse']['GetClientCompaniesResult']['ClientCompanyExtract'][$i]['ClientCompanyID']){
									$elected="selected";
								}
							?>
							
							<option <?php echo $elected ;?> value="<?php echo $array['soap:Envelope']['soap:Body']['GetClientCompaniesResponse']['GetClientCompaniesResult']['ClientCompanyExtract'][$i]['ClientCompanyID']?>"><?php echo $array['soap:Envelope']['soap:Body']['GetClientCompaniesResponse']['GetClientCompaniesResult']['ClientCompanyExtract'][$i]['ClientCompanyName']?></option>
							<?php
						}
					?>
				</select>
				<script>
					loadDoc('<?php echo $saved[0] ;?>','<?php echo $contact[0]?>')  ;
				</script>
			</td>
		</tr>
		<tr>
			<th><label for="year_of_birth">Contacts</label></th>
			<td><div id="demo"></div></td>
		</tr>
	</table>
	<?php
}
    add_action( 'edit_user_profile_update', 'crf_update_profile_fields' );

function crf_update_profile_fields( $user_id ) {
	 
	if ( ! empty( $_POST['company_id'] )  ) {
		update_user_meta( $user_id, 'company_id', intval( $_POST['company_id'] ) );
	}
	 
	if ( ! empty( $_POST['contacts_id'] ) ) {
		update_user_meta( $user_id, 'contacts_id', intval( $_POST['contacts_id'] ) );
	}
	
	
}
?>
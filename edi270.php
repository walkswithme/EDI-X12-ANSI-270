<?php
/**
 * @version    : 1.0.0
 * @package    EDI X12 ANSI 5010
 * @author     Jobin Jose <jobinjose01@gmail.com>
 * @copyright  WWM Support, http://www.walkswithme.net
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */ 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


	/****************** Element data seperator		**************/
	$eleDataSep		= "*";

	/****************** Segment Terminator			**************/
	$segTer			= "~"; 	

	/****************** Component Element seperator **************/
	$compEleSep		= ":"; //or  ^ 	

	/**************** SEGMENT FUNCTION START *********************/
	
	/* ISA Segment  - EDI-270 format */

	/* ISA05/ISA07
		01 means you're using your duns number.
		zz means you're using something mutually defined.
		12 means telephone number.
		14 means duns number with a suffix (if i remember correctly).	

		 Patient’s First Name (NM104)
		 Patient’s Last Name (NM103)
		 Patient’s Date of Birth (DMG02)
		 Subscriber ID Number exactly as it appears on the Anthem ID card including alpha prefi x, if
		 applicable (NM109)
		 Dates of Eligibility requested by Provider (DTP03)	

	*/
	function create_ISA($row,$segTer,$compEleSep) {

		$ISA	 =	array();

		$ISA[0] = "ISA";							/* Interchange Control Header Segment ID */
		
		$ISA[1] = "00";								/* Author Info Qualifier */
		
		$ISA[2] = str_pad("0000000",10," ");		/* Author Information */
		
		$ISA[3] = "00";								/*	Security Information Qualifier
														MEDI-CAL NOTE: For Leased-Line & Dial-Up use '01', 
														for BATCH use '00'.
														'00' No Security Information Present 
														(No Meaningful Information in I04)*/

		$ISA[4] = str_pad("0000000000",10," ");		/* Security Information */
		
		$ISA[5] = str_pad("ZZ",2," ");				/* Interchange ID Qualifier */
		
		$ISA[6] = str_pad($row['x12_sender_id'],15," ");		/* INTERCHANGE SENDER ID */
		
		$ISA[7] = str_pad("ZZ",2," ");				/* Interchange ID Qualifier */
		
		$ISA[8] = str_pad($row['payer_code'],15," ");		/* INTERCHANGE RECEIVER ID */ 
		
		$ISA[9] = str_pad(date('ymd'),6," ");		/* Interchange Date (YYMMDD) */
		
		$ISA[10] = str_pad(date('Hi'),4," ");		/* Interchange Time (HHMM) */
		
		$ISA[11] = str_pad("^",1," ");				/* Interchange Control Standards Identifier */
		
		$ISA[12] = str_pad(substr($row['x12_version'],0,5),5," ");	/* Interchange Control Version Number */
		
		$ISA[13] = str_pad("000000001",9," ");		/* INTERCHANGE CONTROL NUMBER  */ 
		
		$ISA[14] = str_pad("1",1," ");			/* Acknowledgment Request [0= not requested, 1= requested] */ 
		
		$ISA[15] =  str_pad("P",1," ");			/* Usage Indicator [ P = Production Data, T = Test Data ] */ 
		
		$ISA['Created'] = implode('*', $ISA);	/* Data Element Separator */

		$ISA['Created'] = $ISA['Created'] ."*";

		$ISA['Created'] = $ISA ['Created'] . $compEleSep. $segTer ; 
		
		return trim($ISA['Created']);
		
	}
	
	/* GS Segment  - EDI-270 format */

	function create_GS($row,$segTer,$compEleSep) {

		$GS	   = array();

		$GS[0] = "GS";								/* Functional Group Header Segment ID */
		
		$GS[1] = "HS";		/* Functional ID Code [ HS = Eligibility, Coverage or Benefit Inquiry (270) ] */
		
		$GS[2] =  $row['x12_sender_id'];    		/* Application Sender’s ID */
		
		$GS[3] =  $row['payer_code'];  				/* Application Receiver’s ID */
		
		$GS[4] = date('Ymd');						/* Date [CCYYMMDD] */
		
		$GS[5] = date('His');						/* Time [HHMM] – Group Creation Time */ 
		
		$GS[6] = "000000002";						/* Group Control Number */
		
		$GS[7] = "X";			/* Responsible Agency Code Accredited Standards Committee X12 ] */
		
		$GS[8] = $row['x12_version'];			/* Version –Release / Industry[ Identifier Code Query */

		$GS['Created'] = implode('*', $GS);			/* Data Element Separator */

		$GS['Created'] = $GS ['Created'] .$segTer ; 
		 
		return trim($GS['Created']);
		
	}

	/* ST Segment  - EDI-270 format */

	function create_ST($row,$segTer,$compEleSep) {

		$ST	   =	array();

		$ST[0] = "ST";								/* Transaction Set Header Segment ID */
		
		$ST[1] = "270";								/* Transaction Set Identifier Code (Inquiry Request) */
		
		$ST[2] = "000000003";						/* Transaction Set Control Number - Must match SE's */
		
		$ST['Created'] = implode('*', $ST);			/* Data Element Separator */

		$ST['Created'] = $ST ['Created'] . $segTer ; 
		 
		return trim($ST['Created']);
				
	}

	/* BHT Segment  - EDI-270 format */

	function create_BHT($row,$segTer,$compEleSep) {

		$BHT	=	array();
		
		$BHT[0] = "BHT";							/* Beginning of Hierarchical Transaction Segment ID */

		$BHT[1] = "0022";							/* Subscriber Structure Code */  

		$BHT[2] = "13";								/* Purpose Code - This is a Request */  

		$BHT[3] = $row['tansaction_id'];					/*  Submitter Transaction Identifier  
														This information is required by the information Receiver 
														when using Real Time transactions. 
														For BATCH this can be used for optional information.*/

		$BHT[4] = str_pad(date('Ymd'),8," ");		/* Date Transaction Set Created */
		
		$BHT[5] = str_pad(date('His'),8," ");		/* Time Transaction Set Created */

		$BHT['Created'] = implode('*', $BHT);		/* Data Element Separator */

		$BHT['Created'] = $BHT ['Created'] . $segTer ; 
		 
		return trim($BHT['Created']);
		
	}

	/* HL Segment  - EDI-270 format */

	function create_HL($row, $nHlCounter,$segTer,$compEleSep) {

		$HL				= array();

		$HL[0]		= "HL";							/* Hierarchical Level Segment ID */
		$HL_LEN[0]	=  2;

		$HL[1] = $nHlCounter;						/* Hierarchical ID No. */
		
		if($nHlCounter == 1)
		{ 
			$HL[2] = ""; 
			$HL[3] = 20;	/* Description: Identifies the payor, maintainer, or source of the information.*/
			$HL[4] = 1;		/* 1 Additional Subordinate HL Data Segment in This Hierarchical Structure. */
		}
		else if($nHlCounter == 2)
		{
			$HL[2] = 1;		/* Hierarchical Parent ID Number */
			$HL[3] = 21;	/* Hierarchical Level Code. '21' Information Receiver*/
			$HL[4] = 1;		/* 1 Additional Subordinate HL Data Segment in This Hierarchical Structure. */
		}
		else
		{
			$HL[2] = 2;
			$HL[3] = 22;	/* Hierarchical Level Code.'22' Subscriber */
			$HL[4] = 0;		/* 0 no Additional Subordinate in the Hierarchical Structure. */
		}
		
		$HL['Created'] = implode('*', $HL);			/* Data Element Separator */

		$HL['Created'] = $HL ['Created'] . $segTer ; 
		 
		return trim($HL['Created']);
	
	}

	/* NM1 Segment  - EDI-270 format */

	function create_NM1($row,$nm1Cast,$segTer,$compEleSep) {

		$NM1		= array();
		
		$NM1[0]		= "NM1";					/* Subscriber Name Segment ID */
		
		if($nm1Cast == 'PR')
		{
			$NM1[1] = "PR";						/* Entity ID Code - Payer [PR Payer] */
			$NM1[2] = "2";						/* Entity Type - Non-Person */
			$NM1[3] = $row["payer_name"];		/* Organizational Name */
			$NM1[4] = "";						/* Data Element not required.*/
			$NM1[5] = "";						/* Data Element not required.*/
			$NM1[6] = "";						/* Data Element not required.*/
			$NM1[7] = "";						/* Data Element not required.*/
			$NM1[8] = "PI";						/* 46 - Electronic Transmitter Identification Number (ETIN) */
			$NM1[9] = $row["payer_code"];	    /* Application Sender’s ID */
		}
		else if($nm1Cast == '1P')
		{
			$NM1[1] = "IP";						/* Entity ID Code - Provider [1P Provider]*/
			$NM1[2] = "1";						/* Entity Type - Person */
			$NM1[3] = "";						/* Organizational Name */
			$NM1[4] = "";						/* Data Element not required.*/
			$NM1[5] = "";						/* Data Element not required.*/
			$NM1[6] = "";						/* Data Element not required.*/
			$NM1[7] = "";						/* Data Element not required.*/
			$NM1[8] = "XX";						
			$NM1[9] = $row['provider_npi'];		/* Patient Doctors NPI*/
		}
		else if($nm1Cast == 'IL')
		{
			$NM1[1] = "IL";						/* Insured or Subscriber */
			$NM1[2] = "1";						/* Entity Type - Person */
			$NM1[3] = $row['subscriber_lname'];			/* last Name	*/
			$NM1[4] = $row['subscriber_fname'];			/* first Name	*/
			$NM1[5] = $row['subscriber_mname'];			/* middle Name	*/
			$NM1[6] = "";						/* data element */
			$NM1[7] = "";						/* data element */
			$NM1[8] = "MI";						/* Identification Code Qualifier */
			$NM1[9] = $row['subscriber_policy_number'];/* Identification Code, Its Insurance Number I think */
		}
		
		$NM1['Created'] = implode('*', $NM1);	/* Data Element Separator */

		$NM1['Created'] = $NM1['Created'] .$segTer ; 
		 
		return trim($NM1['Created']);

	}

	/* REF Segment  - EDI-270 format */

	function create_REF($row,$ref,$segTer,$compEleSep) {

		$REF	=	array();
	
		$REF[0] = "REF";						/* Subscriber Additional Identification */
	
		if($ref == '1P')
		{
			$REF[1] = "4A";						/* Reference Identification Qualifier */
			$REF[2] = $row['provider_pin'];		/* Provider Pin. */
		}
		else
		{
			$REF[1] = "EJ";						/* 'EJ' for Patient Account Number */
			$REF[2] = $row['pubpid'];			/* Patient Account No. */
		}
		$REF['Created'] = implode('*', $REF);	/* Data Element Separator */

		$REF['Created'] = $REF['Created'] .$segTer ; 
		 
		return trim($REF['Created']);
	  
	}

	/* TRN Segment - EDI-270 format */

	function create_TRN($row,$tracno,$refiden,$segTer,$compEleSep) {

		$TRN	=	array();

		$TRN[0] = "TRN";								/* Subscriber Trace Number Segment ID */

		$TRN[1] = "1";									/* Trace Type Code – Current Transaction Trace Numbers */

		$TRN[2] = $tracno;								/* Trace Number */

		$TRN[3] = "9000000000";							/* Originating Company ID – must be 10 positions in length */

		$TRN[4] = $refiden;								/* Additional Entity Identifier (i.e. Subdivision) */

		$TRN['Created'] = implode('*', $TRN);			/* Data Element Separator */

		$TRN['Created'] = $TRN['Created'] . $segTer ; 
		 
		return trim($TRN['Created']);
	  
	}

	/* DMG Segment - EDI-270 format */
	
	function create_DMG($row,$segTer,$compEleSep) {

		$DMG	=	array();
		
		$DMG[0] = "DMG";								/* Date or Time or Period Segment ID */

		$DMG[1] = "D8";									/* Date Format Qualifier - (D8 means CCYYMMDD) */

		$DMG[2] = $row['subscriber_dob'];				/* Subscriber's Birth date */

		$DMG[3] = $row['subscriber_gender'];			/* Subscriber's Gender M/F */

		$DMG['Created'] = implode('*', $DMG);			/* Data Element Separator */

		$DMG['Created'] = $DMG['Created'] . $segTer ; 
		 
		return trim($DMG['Created']);			
	}

	/* DTP Segment - EDI-270 format */
	
	function create_DTP($row,$qual,$segTer,$compEleSep) {

		$DTP	=	array();
		
		$DTP[0] = "DTP";								/* Date or Time or Period Segment ID */
		
		$DTP[1] = $qual;								/* Qualifier - Date of Service */
		
		$DTP[2] = "D8";									/* Date Format Qualifier - (D8 means CCYYMMDD) */
		
		if($qual == '102'){
			$DTP[3] = $row['date'];						/* Date */
		}else{
			$DTP[3] = $row['date_of_service'];				/* Date of Service */
		}
		$DTP['Created'] = implode('*', $DTP);			/* Data Element Separator */

		$DTP['Created'] = $DTP['Created'] . $segTer ; 
		 
		return trim($DTP['Created']);
	}
	
	/* EQ Segment - EDI-270 format */
	
	function create_EQ($row,$segTer,$compEleSep) {

		$EQ		=	array();
		
		$EQ[0]	= "EQ";									/* Subscriber Eligibility or Benefit Inquiry Information */
		
		$EQ[1]	= "30";									/* Service Type Code */
		
		$EQ['Created'] = implode('*', $EQ);				/* Data Element Separator */

		$EQ['Created'] = $EQ['Created'] . $segTer ; 
		 
		return trim($EQ['Created']);
	}
	
	/* SE Segment - EDI-270 format */
	
	function create_SE($row,$segmentcount,$segTer,$compEleSep) {

		$SE				=	array();
		
		$SE[0] = "SE";									/* Transaction Set Trailer Segment ID */

		$SE[1] = $segmentcount;							/* Segment Count */

		$SE[2] = "000000003";							/* Transaction Set Control Number - Must match ST's */

		$SE['Created'] = implode('*', $SE);				/* Data Element Separator */

		$SE['Created'] = $SE['Created'] .$segTer ; 
		 
		return trim($SE['Created']);
	}
	
	/* GE Segment - EDI-270 format */
	
	function create_GE($row,$segTer,$compEleSep) {

		$GE		=	array();
		
		$GE[0]	= "GE";									/* Functional Group Trailer Segment ID */

		$GE[1]	= "1";									/* Number of included Transaction Sets */

		$GE[2]	= "000000002";							/* Group Control Number */

		$GE['Created'] = implode('*', $GE);				/* Data Element Separator */

		$GE['Created'] = $GE['Created'] . $segTer ; 
		 
		return trim($GE['Created']);
	}
	
	/* IEA Segment - EDI-270 format */

	function create_IEA($row,$segTer,$compEleSep) {

		$IEA	=	array();
		
		$IEA[0] = "IEA";								/* Interchange Control Trailer Segment ID */

		$IEA[1] = "1";									/* Number of included Functional Groups */

		$IEA[2] = "000000001";							/* Interchange Control Number */

		$IEA['Created'] = implode('*', $IEA);

		$IEA['Created'] = $IEA['Created'] . $segTer ; 
		 
		return trim($IEA['Created']);
	}

	function translate_relationship($relationship) {
		switch ($relationship) {
			case "spouse":
				return "01";
				break;
			case "child":
				return "19";
				break;
			case "self":
			default:
				return "S";
		}
	}

	/* EDI-270 Batch file Generation */

	function createEDIFile($res,$segTer,$compEleSep){
		

			
			$i=1;

			$PATEDI	   = "";

			/***************** For Header Segment ****************************/

			$nHlCounter = 1;
			$rowCount	= 0;
			$trcNo		= 1234501;
			$refiden	= 5432101;
			
			/*while ($row = sqlFetchArray($res))*/
			foreach($res as $row) 
			{
				
				if($nHlCounter == 1)
				{
					/* create ISA */
					$PATEDI	   = create_ISA($row,$segTer,$compEleSep).PHP_EOL;
					
					/* create GS */
					$PATEDI	  .= create_GS($row,$segTer,$compEleSep).PHP_EOL;

					/* create ST */
					$PATEDI	  .= create_ST($row,$segTer,$compEleSep).PHP_EOL;
					
					/* create BHT */
					$PATEDI	  .= create_BHT($row,$segTer,$compEleSep).PHP_EOL;
					
					/***************** For Payer Segment ****************************/
						
					$PATEDI  .= create_HL($row,1,$segTer,$compEleSep).PHP_EOL;
					$PATEDI  .= create_NM1($row,'PR',$segTer,$compEleSep).PHP_EOL;

					/***************** For Provider Segment *************************/				
							
					$PATEDI  .= create_HL($row,2,$segTer,$compEleSep).PHP_EOL;
					$PATEDI  .= create_NM1($row,'1P',$segTer,$compEleSep).PHP_EOL;
					//$PATEDI  .= create_REF($row,'1P',$segTer,$compEleSep);

					$nHlCounter = $nHlCounter + 2;	
					$segmentcount = 7; /* segement counts - start from ST */
				}

				/***************** For Subscriber Segment ***********************/				
				
				$PATEDI  .= create_HL($row,$nHlCounter,$segTer,$compEleSep).PHP_EOL;
				$PATEDI  .= create_TRN($row,$trcNo,$refiden,$segTer,$compEleSep).PHP_EOL;
				$PATEDI  .= create_NM1($row,'IL',$segTer,$compEleSep).PHP_EOL;
				//$PATEDI  .= create_REF($row,'IL',$segTer,$compEleSep);
				$PATEDI  .= create_DMG($row,$segTer,$compEleSep).PHP_EOL;
				$PATEDI  .= create_DTP($row,'307',$segTer,$compEleSep).PHP_EOL;//102
				//$PATEDI  .= create_DTP($row,'472',$segTer,$compEleSep);
				$PATEDI  .= create_EQ($row,$segTer,$compEleSep).PHP_EOL;
										
				$segmentcount = $segmentcount + 8;
				$nHlCounter = $nHlCounter + 1;
				$rowCount	= $rowCount + 1;
				$trcNo		= $trcNo + 1;
				$refiden	= $refiden + 1;
				

				if($rowCount == sizeof($res))
				{
					$segmentcount = $segmentcount + 1;
					$PATEDI	  .= create_SE($row,$segmentcount,$segTer,$compEleSep).PHP_EOL;
					$PATEDI	  .= create_GE($row,$segTer,$compEleSep).PHP_EOL;
					$PATEDI	  .= create_IEA($row,$segTer,$compEleSep).PHP_EOL;
				}
			}
	
			$file = fopen($row['tansaction_id'].".txt","w");
			fwrite($file,$PATEDI);
			fclose($file);
	}
	


	


	$res[] =   [
					'tansaction_id' => '000001' 			/*Unique Transaction ID for each request */
					,'date_of_service' => '20171028'        /* Date Of service */ 
					,'subscriber_lname' => '***'			/* Subscriber/Patient last name */ 
					,'subscriber_fname' => '***'			/* Subscriber/Patient First name */ 
					,'subscriber_mname' => '**'			/* Subscriber/Patient middle name */ 
					,'subscriber_dob'=>'***'			/* Subscriber/Patient DOB  YYYYMMDD*/ 
					,'subscriber_policy_number' => '***' /* Subscriber/Patient Policy Number*/ 
					,'subscriber_gender' => 'M'				/* Subscriber/Patient Gender M/F/U */ 
					,'date' =>'20171028'					/* Interchange Date  YYYYMMDD*/
					,'provider_npi' => '***'			/* Doctor NPI*/
					,'x12_sender_id' => '***'				
					,'payer_code' => '***' 				/* Insurance Payer Code*/
					,'x12_version' => '005010X279A1'
					,'payer_name' => '****'				/* Insurance Payer Name*/

				];

	

	createEDIFile($res,$segTer,$compEleSep);
	echo "270 File is Ready !";

	
?>


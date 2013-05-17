<?php
$file = $_SERVER['argv'][1];

// Open the input stream
$buffer = 9999999999999;
$delim = '<?xml version="1.0" encoding="UTF-8"?>';
$fp = fopen($file, 'r');
print stream_get_line($fp, $buffer, $delim);

/*
patent (Patent Number)
kind (Kind of Patent) 
claims (Number of Claims)
apptype (Application Type)
appnum (Application Number)
gdate (Grant Date)
appdate (Application Filing Date)
*/

// Open up various CSV streams:
$patents = fopen($file . "-patents.csv", 'w+');
fputcsv($patents, array(
  "patent",
  "kind",
  "claims",
  "apptype",
  "appnum",
  "gdate",
  "appdate",
));

/*
patdesc:
  -patent (Patent Number)
  abstract (Abstract)
  title (Title)
*/
$patent_descriptions = fopen($file . "-patdesc.csv", 'w+');
fputcsv($patent_descriptions, array(
  "patent",
  "abstract",
  "title"
));


/*
class:
  -patent (Patent Number)
  -prim (Primary 0/1 Indicator Variable)
  -class (Current U.S. Class)
  -subclass (Current U.S. Subclass)
*/
$classes = fopen($file . "-class.csv", 'w+');
fputcsv($patents, array(
  "patent",
  "prim",
  "class",
  "subclass"
));


/*
  -patent (Citing Patent Number)
  -cited (Cited Patent Number)
*/
$references = fopen($file . "-citations.csv", 'w+');
fputcsv($references, array(
  "patent",
  "cited"
));

$classifications = fopen($file . "-classifications.csv", 'w+');
fputcsv($classifications, array(
  "patent",
  "Country",
  "Classification",
  "Info"
));


/*
inventors:
  -patent (Patent Number)
  -invseq (Sequence Number)
  firstname (First Name)
  lastname (Last Name)
  street (Street)
  city (City)
  state (State)
  country (Country)
  zip (Zip Code)
*/

$applicants = fopen($file . "-inventors.csv", 'w+');
fputcsv($applicants, array(
  "patent",
  "invseq",
  "firstname",  
  "lastname",
  //"Organisation",
  "Street",
 

  "City",
  "State",
  "Country",
  "Zip",
  "Type",
  "Nationality",
  "Residence"
));

$agents = fopen($file . "-agents.csv", 'w+');
fputcsv($agents, array(
  "patent", 
  "Organisation", 
  "Type"
));

/*
assignees:
  -patent (Patent Number)
  -asgseq (Sequence Number)
  asgtype (Assignee Type)
  assignee (Name)
  city (City)
  state (State)
  country (Country)
  zip (Zip Code)
*/
$assignees = fopen($file . "-assignees.csv", 'w+');
fputcsv($assignees, array(
  "patent",
  "asgseq",
  "asgtype",
  "assignee",
  "City",
  "State",
  "Country",
  "zip"
));

$examiners = fopen($file . "-examiners.csv", 'w+');
fputcsv($examiners, array(
  "patent",
  "Primary Last Name",
  "Primary First Name",
  "Primary Department",
  "Assistant First Name",
  "Assistant Last Name",
  "Assistant Department",
));



// Parse entries
while (($doc = simplexml_load_string(stream_get_line($fp, $buffer, $delim))) !== false) {

  $grant = $doc->{'us-bibliographic-data-grant'};

  $application_document = $grant->{'application-reference'}->{'document-id'};
  $publication_document = $grant->{'publication-reference'}->{'document-id'};

  //$country = (string)$application_document->country;
  $application_id = (string)$application_document->{'doc-number'};
  $patent_id = (string)$publication_document->{'doc-number'};



  // Write
  /*
  class:
  -patent (Patent Number)
  -prim (Primary 0/1 Indicator Variable)
  -class (Current U.S. Class)
  -subclass (Current U.S. Subclass)
  */
  fputcsv($classes, array(
    $patent_id,
    (string)$grant->{'us-application-series-code'},
    (string)$grant->{'classification-national'}->{'main-classification'}, // TBC!
    (string)$grant->{'classification-locarno'}->{'edition'},// TBC!
    (string)$grant->{'classification-locarno'}->{'main-classification'},// TBC!
  ));

  /*
  patents:
    patent (Patent Number)
    kind (Kind of Patent) 
    claims (Number of Claims)
    apptype (Application Type)
    appnum (Application Number)
    gdate (Grant Date)
    appdate (Application Filing Date)
  */ 
  fputcsv($patents, array(
      $patent_id,
      (string)$publication_document->kind,
      (string)$grant->{'number-of-claims'},
      (string)$application_document->type,
      $application_id,
      (string)$publication_document->date,
      (string)$application_document->date,
      //(string)$application_document->country,
      //
  ));

  /*
  patdesc:
    -patent (Patent Number)
    abstract (Abstract)
    title (Title)
  */
  fputcsv($patent_descriptions, array(
    $patent_id,
    (string)$doc->abstract->p,
    (string)$grant->{'invention-title'}
  ));

  foreach ($grant->parties as $party) {
    foreach ($party->agents as $agent) {
      fputcsv($agents, array(
        $patent_id,
        (string)$agent->agent->addressbook->orgname,
        (string)$agent->agent['rep-type']
      ));
    }

    /*
    inventors:
      -patent (Patent Number)
      -invseq (Sequence Number)
      firstname (First Name)
      lastname (Last Name)
      street (Street)
      city (City)
      state (State)
      country (Country)
      zip (Zip Code)
    */

    foreach ($party->applicants as $applicant) {
      fputcsv($applicants, array(
        $patent_id,
        (string)$applicant->applicant['sequence'],
        //(string)$applicant->applicant->addressbook->orgname,
        (string)$applicant->applicant->addressbook->{'last-name'},
        (string)$applicant->applicant->addressbook->{'first-name'},
        (string)$applicant->applicant->addressbook->address->city,
        (string)$applicant->applicant->addressbook->address->state,
        (string)$applicant->applicant->addressbook->address->country,
        (string)$applicant->applicant->addressbook->address->zip,
        (string)$applicant->applicant['app-type'],
        (string)$applicant->applicant->nationality->country,
        (string)$applicant->applicant->residence->country
      ));
    }    
  }


  foreach ($grant->assignees as $assignee) {
    /*
    assignees:
      -patent (Patent Number)
      -asgseq (Sequence Number)
      asgtype (Assignee Type)
      assignee (Name)
      city (City)
      state (State)
      country (Country)
      zip (Zip Code)
    */

    fputcsv($assignees, array(
      $patent_id,
      "asgseq",
      @(string)$assignee->assignee->addressbook->role, // TBC
      @(string)$assignee->assignee->addressbook->orgname,
      @(string)$assignee->assignee->addressbook->address->city,
      @(string)$assignee->assignee->addressbook->address->state,
      @(string)$assignee->assignee->addressbook->address->country,
      @(string)$assignee->assignee->addressbook->address->zip
    ));
  }



  fputcsv($examiners, array(
    $patent_id,
    (string)$grant->examiners->{'primary-examiner'}->{'last-name'},
    (string)$grant->examiners->{'primary-examiner'}->{'first-name'},
    (string)$grant->examiners->{'primary-examiner'}->department,
    (string)$grant->examiners->{'assistant-examiner'}->{'last-name'},
    (string)$grant->examiners->{'assistant-examiner'}->{'first-name'},
    (string)$grant->examiners->{'assistant-examiner'}->department,
  ));

  foreach ($grant->{'us-field-of-classification-search'}->{'classification-national'} as $classification) {
    fputcsv($classifications, array(
      $patent_id,
      (string)$classification->country,
      (string)$classification->{'main-classification'},
      (string)$classification->{'additional-info'}
    ));
  }

  if ($grant->{'references-cited'}) {
    foreach ($grant->{'references-cited'}->citation as $citation) {
      fputcsv($references, array(
        $patent_id,
        @(string)$citation->patcit->{'document-id'}->country,
        @(string)$citation->patcit->{'document-id'}->{'doc-number'},
        @(string)$citation->patcit->{'document-id'}->kind,
        @(string)$citation->patcit->{'document-id'}->name,
        @(string)$citation->patcit->{'document-id'}->date,
        @(string)$citation->patcit->category,
        @(string)$citation->patcit->{'classification-national'}->country,
        @(string)$citation->patcit->{'classification-national'}->{'main-classification'}
      ));
    }
  }
  //print_r($grant);
}
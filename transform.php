<?php
$file = $_SERVER['argv'][1];

// Open the input stream
$buffer = 9999999999999;
$delim = '<?xml version="1.0" encoding="UTF-8"?>';
$fp = fopen($file, 'r');
print stream_get_line($fp, $buffer, $delim);



// Open up various CSV streams:
$patents = fopen($file . "-patents.csv", 'w+');
fputcsv($patents, array(
  "Application ID",
  "Country",
  "Date",
  "Title"
));

$references = fopen($file . "-references.csv", 'w+');
fputcsv($references, array(
  "Application ID"
));

$classifications = fopen($file . "-classifications.csv", 'w+');
fputcsv($classifications, array(
  "Application ID",
  "Country",
  "Classification",
  "Info"
));

$applicants = fopen($file . "-applicants.csv", 'w+');
fputcsv($applicants, array(
  "Application ID",
  "Organisation",
  "Last Name",
  "First Name",
  "City",
  "State",
  "Country",
  "Type",
  "Nationality",
  "Residence"
));

$agents = fopen($file . "-agents.csv", 'w+');
fputcsv($agents, array(
  "Application ID", 
  "Organisation", 
  "Type"
));

$assignees = fopen($file . "-assignees.csv", 'w+');
fputcsv($assignees, array(
  "Application ID",
  "Organisation",
  "City",
  "State",
  "Country"
));

$examiners = fopen($file . "-examiners.csv", 'w+');
fputcsv($examiners, array(
  "Application ID",
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

  //$country = (string)$application_document->country;
  $application_id = (string)$application_document->{'doc-number'};



  // Write
  fputcsv($patents, array(
      $application_id,
      (string)$application_document->country,
      (string)$application_document->date,
      (string)$grant->{'invention-title'}
  ));

  foreach ($grant->parties as $party) {
    foreach ($party->agents as $agent) {
      fputcsv($agents, array(
        $application_id,
        (string)$agent->agent->addressbook->orgname,
        (string)$agent->agent['rep-type']
      ));
    }

    foreach ($party->applicants as $applicant) {
      fputcsv($applicants, array(
        $application_id,
        (string)$applicant->applicant->addressbook->orgname,
        (string)$applicant->applicant->addressbook->{'last-name'},
        (string)$applicant->applicant->addressbook->{'first-name'},
        (string)$applicant->applicant->addressbook->address->city,
        (string)$applicant->applicant->addressbook->address->state,
        (string)$applicant->applicant->addressbook->address->country,
        (string)$applicant->applicant['app-type'],
        (string)$applicant->applicant->nationality->country,
        (string)$applicant->applicant->residence->country
      ));
    }    
  }


  foreach ($grant->assignees as $assignee) {

    fputcsv($assignees, array(
      $application_id,
      @(string)$assignee->assignee->addressbook->orgname,
      @(string)$assignee->assignee->addressbook->address->city,
      @(string)$assignee->assignee->addressbook->address->state,
      @(string)$assignee->assignee->addressbook->address->country  
    ));
  }



  fputcsv($examiners, array(
    $application_id,
    (string)$grant->examiners->{'primary-examiner'}->{'last-name'},
    (string)$grant->examiners->{'primary-examiner'}->{'first-name'},
    (string)$grant->examiners->{'primary-examiner'}->department,
    (string)$grant->examiners->{'assistant-examiner'}->{'last-name'},
    (string)$grant->examiners->{'assistant-examiner'}->{'first-name'},
    (string)$grant->examiners->{'assistant-examiner'}->department,
  ));

  foreach ($grant->{'us-field-of-classification-search'}->{'classification-national'} as $classification) {
    fputcsv($classifications, array(
      $application_id,
      (string)$classification->country,
      (string)$classification->{'main-classification'},
      (string)$classification->{'additional-info'}
    ));
  }

  if ($grant->{'references-cited'}) {
    foreach ($grant->{'references-cited'}->citation as $citation) {
      fputcsv($references, array(
        $application_id,
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
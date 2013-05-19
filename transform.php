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
fputcsv($classes, array(
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
  "Residence",
  "generated_sort"
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
  "zip",
  "generated_sort"
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


// See http://www.uspto.gov/products/Patent_Grant_XML_v4.3.pdf page 96
function split_classification($main_classification) {
  $class = substr($main_classification, 0, 3);
  $subclass[] = substr($main_classification, 3, 3);

  if (strlen($main_classification) > 6) {
    $subclass[] = substr($main_classification, 6, strlen($main_classification));
  }

  return array($class, implode(".", $subclass));
}

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

  $main_classification = (string)$grant->{'classification-national'}->{'main-classification'};
  list($class, $subclass) = split_classification($main_classification);
  fputcsv($classes, array(
    $patent_id,
    1,
    $class, // TBC!
    $subclass, // TBC!

    // (string)$grant->{'classification-locarno'}->{'edition'},// TBC!
    // (string)$grant->{'classification-locarno'}->{'main-classification'},// TBC!
  ));

  // And further classifications

  foreach ($grant->{'classification-national'}->{'further-classification'} as $further_classification) { 
    list($class, $subclass) = split_classification((string)$further_classification);
    fputcsv($classes, array(
      $patent_id,
      0,
      $class, // TBC!
      $subclass, // TBC!

      // (string)$grant->{'classification-locarno'}->{'edition'},// TBC!
      // (string)$grant->{'classification-locarno'}->{'main-classification'},// TBC!
    ));
  }
    /*
<classification-national>
<country>US</country>
<main-classification> 4060701</main-classification>
<further-classification>428 345</further-classification>
<further-classification>428 346</further-classification>
<further-classification>428 347</further-classification>
<further-classification>428 357</further-classification>
<further-classification>428 361</further-classification>
<further-classification>428 363</further-classification>
<further-classification>15624413</further-classification>
</classification-national>
    */

/*
Patent 
Class
Subclass
Primary

08341860
40
607.01 
1

08341860
156
244.13
0

08341860
428
34.5
0

08341860
428
34.6
0

08341860
428
34.7
0

08341860
428
35.7
0

08341860
428
36.1
0

08341860
428
36.3
0

*/


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
      (string)$grant->{'us-application-series-code'}, //(string)$application_document->type,
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
    strip_tags(trim((string)$doc->abstract)),
    (string)$grant->{'invention-title'},
    trim((string)$doc->abstract)
  ));

  foreach ($grant->parties as $party) {
    if ($party->agents) {
      foreach ($party->agents as $agent) {
        fputcsv($agents, array(
          $patent_id,
          (string)$agent->agent->addressbook->orgname,
          (string)$agent->agent['rep-type']
        ));
      }
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

    $n = 0;
    foreach ($party->applicants->applicant as $applicant) {
      $n++;
      fputcsv($applicants, array(
        $patent_id,
        (string)$applicant['sequence'],
        //(string)$applicant->applicant->addressbook->orgname,
        (string)$applicant->addressbook->{'last-name'},
        (string)$applicant->addressbook->{'first-name'},
        (string)$applicant->addressbook->address->street,
        (string)$applicant->addressbook->address->city,
        (string)$applicant->addressbook->address->state,
        (string)$applicant->addressbook->address->country,
        (string)$applicant->addressbook->address->zip,
        (string)$applicant['app-type'],
        (string)$applicant->nationality->country,
        (string)$applicant->residence->country,
        $n
      ));
    }    
  }

  $n = 0;
  if ($grant->assignees->assignee) {
    foreach ($grant->assignees->assignee as $assignee) {
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
      $n++;

      fputcsv($assignees, array(
        $patent_id,
        "", //asgseq doesn't really exist
        @(string)$assignee->addressbook->role, // TBC
        @(string)$assignee->addressbook->orgname,
        @(string)$assignee->addressbook->address->city,
        @(string)$assignee->addressbook->address->state,
        @(string)$assignee->addressbook->address->country,
        @(string)$assignee->addressbook->address->zip,
        $n
      ));
    }
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

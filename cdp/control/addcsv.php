<?php
global $objMetadata, $objCdp, $entryMessage;

// We check the file name
if ($_FILES ['csv'] ['tmp_name']) {
  $csvfile = $_FILES ['csv'] ['tmp_name'];
  $data_array = file ( $csvfile );
  
  // The first line defines the keywords
  $keywords = $data_array [0];
  
  $keys_array = explode ( "|", $keywords );
  
  // We check if the first keyword is 'Filename'
  if (strtoupper ( $keys_array [0] ) != "FILENAME") {
    $entryMessage = "Problem importing CSV file! The first keyword on should be Filename!";
    $_GET ['indexAction'] = 'import_csv_file';
    return;
  }
  
  $correctCSV = true;
  // We check the rest of the keywords. If they don't exist, we return with an error.
  for($i = 1; $i < sizeof ( $keys_array ); $i ++) {
    if ($objMetadata->hasKey ( rtrim ( $keys_array [$i] ) )) {
    } else {
      $correctCSV = false;
    }
  }
  
  // Check if all mandatory keywords are in the CSV file.
  foreach ( $objMetadata->getKeys () as $key => $value ) {
    if ($objMetadata->isRequired ( $value ['id'] )) {
      // If the required metadata is not in the csv file, we return with an error.
      if (array_search ( $value ['id'], $keys_array )) {
      } else {
        $entryMessage = "Problem importing CSV file : keyword <strong>" . $value ['id'] . "</strong> is not available in the CSV file.";
        $_GET ['indexAction'] = "import_csv_file";
        return;
      }
    }
  }
  
  if (! $correctCSV) {
    $entryMessage = "Incorrect keywords in the CSV file!";
    $_GET ['indexAction'] = 'import_csv_file';
  } else {
    for($i = 1; $i < sizeof ( $data_array ); $i ++) {
      // All keywords are correct, we read the first line.
      $line = explode ( "|", $data_array [$i] );
      $filename = $line [0];
      $delivery = $line [array_search ( "DELIVERY", $keys_array )];
      
      // Check if the file exists... We only add the files which are available on the ftp server.
      if ($objCdp->existOnFtpServer ( $filename )) {
        // We deliver the files.
        $objCdp->deliver_file ( $filename, $delivery );
        
        for($j = 1; $j < sizeof ( $keys_array ); $j ++) {
          if (strtoupper ( str_replace ( ' ', '_', trim ( $keys_array [$j] ) ) ) != "DELIVERY") {
            // Check if the keywords have a valid value.
            $keyToAdd = str_replace ( ' ', '_', trim ( $keys_array [$j] ) );
            $valueToAdd = trim ( $line [$j] );
            
            if (trim ( $line [$j] ) != '') {
              if ($objMetadata->isValidValue ( trim ( $keys_array [$j] ), $valueToAdd )) {
                $objCdp->addKey ( $filename, $keyToAdd, $valueToAdd );
              } else {
                $entryMessage = "Aborted importing CSV file at file <strong>" . $filename . "</strong>!<br />Invalid value <strong>" . $valueToAdd . "</strong> for keyword <strong>" . trim ( $keys_array [$j] ) . "</strong>";
                $_GET ['indexAction'] = 'import_csv_file';
                return;
              }
            }
          }
        }
        // Add the size of the file
        $objCdp->addKey ( $filename, "size", $objCdp->getSizeFromFtp ( $filename ) );
      }
    }
  }
} else {
  $entryMessage = "Problem importing CSV file!";
  $_GET ['indexAction'] = 'import_csv_file';
}
?>
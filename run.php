<?php 
require 'vendor/autoload.php';

use Kreait\Firebase\Configuration;
use Kreait\Firebase\Firebase;

$config = new Configuration();
$config->setAuthConfigFile(__DIR__.'/auth/wavelength.json');

$firebase = new Firebase('https://wavelength-d78bb.firebaseio.com', $config);

$reference = $firebase->getReference('/');

$all = $reference->getData();

$link = mysql_connect('127.0.0.1', 'root', 'stangetz');
mysql_set_charset('utf8',$link);

if ($link) {
    foreach ($all as $name => $data){

        switch ($name){
            case 'activities':
                insertActivitiesAffiliations($name, $data);
                break;
            case 'affiliations':
                insertActivitiesAffiliations($name, $data);
                break;

            case 'user_profiles':
                insertProfiles($data);
                break;

        }
    }

}

function insertActivitiesAffiliations($tableName, $data){

    $query = 'DROP TABLE IF EXISTS ' . $tableName;

    $result = mysql_query($query);

    $query = <<<EOD
      CREATE TABLE IF NOT EXISTS $tableName (
        id STRING PRIMARY KEY,
        icon STRING,
        name STRING,
        uid STRING
        )
EOD;
    $result = mysql_query($query);

    $query = '';
    foreach ($data as $id => $fields){

        $fields['icon'] = SQLite3::escapeString($fields['icon']);
        $fields['name'] = SQLite3::escapeString($fields['name']);
        $fields['uid'] = SQLite3::escapeString($fields['uid']);

        $query.="INSERT INTO $tableName(id, icon, name, uid) VALUES('$id','{$fields['icon']}','{$fields['name']}','{$fields['uid']}'); \n";


    }

    echo $query;
    $result = mysql_query($query);

}



function insertProfiles($data){

    $query = 'DROP TABLE IF EXISTS user_profiles';
    $result = mysql_query($query);

    $query = 'DROP TABLE IF EXISTS user_activities';
    $result = mysql_query($query);

    $query = 'DROP TABLE IF EXISTS user_affiliations';
    $result = mysql_query($query);

    $query = <<<EOD
      CREATE TABLE IF NOT EXISTS user_profiles (
        uid VARCHAR(50) PRIMARY KEY,
        description TEXT,
        email VARCHAR(50),
        latitude VARCHAR(50),
        longitude VARCHAR(50),
        first_name VARCHAR(50), 
        last_name VARCHAR(50),
        current_city VARCHAR(50),
        current_state VARCHAR(50),
        current_county VARCHAR(50),
        current_country VARCHAR(50),
        status VARCHAR(50)
        )
EOD;
    $result = mysql_query($query);

    $query = <<<EOD
      CREATE TABLE IF NOT EXISTS user_activities (
        uid VARCHAR(50),
        activity_id VARCHAR(50)
        )
EOD;
    $result = mysql_query($query);


    $query = <<<EOD
      CREATE TABLE IF NOT EXISTS user_affiliations (
        uid VARCHAR(50),
        affiliation_id VARCHAR(50)
        )
EOD;
    $result = mysql_query($query);

    $query = '';
    foreach ($data as $uid => $fields){

        $fields['description'] = SQLite3::escapeString($fields['description']);
        $fields['email'] = SQLite3::escapeString($fields['email']);
        $fields['first_name'] = SQLite3::escapeString($fields['first_name']);

        $fields['last_name'] = SQLite3::escapeString($fields['last_name']);
        $fields['latitude'] = SQLite3::escapeString($fields['latitude']);
        $fields['longitude'] = SQLite3::escapeString($fields['longitude']);

        $fields['current_city'] = SQLite3::escapeString($fields['current_city']);
        $fields['current_state'] = SQLite3::escapeString($fields['current_state']);
        $fields['current_county'] = SQLite3::escapeString($fields['current_county']);

        $fields['current_state'] = SQLite3::escapeString($fields['current_state']);
        $fields['status'] = SQLite3::escapeString($fields['status']);

        $query.="INSERT INTO user_profiles(uid, description, email, first_name,last_name,latitude,longitude,current_city, current_state,current_county, current_country, status) VALUES('$uid','{$fields['description']}','{$fields['email']}','{$fields['first_name']}', '{$fields['last_name']}', '{$fields['latitude']}', '{$fields['longitude']}', '{$fields['location']['city']}', '{$fields['location']['state']}', '{$fields['location']['county']}', '{$fields['location']['country']}', '{$fields['status']}'); \n";



        $sql = '';
        foreach ($fields['activities'] as $activity=>$item){

            $sql.="INSERT INTO user_activities(uid, activity_id) VALUES('$uid','{$activity}'); \n";

        }
        echo $sql;
        $result = mysql_query($sql);

        $sql = '';
        foreach ($fields['affiliations'] as $id=>$item){

            $sql.="INSERT INTO user_affiliations(uid, affiliation_id) VALUES('$uid','{$id}'); \n";

        }
        echo $sql;
        $result = mysql_query($sql);
    }

    echo $query;
    $result = mysql_query($query);

}

echo 'Done';







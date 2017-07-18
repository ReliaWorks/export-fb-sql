<?php 
require 'vendor/autoload.php';

use Kreait\Firebase\Configuration;
use Kreait\Firebase\Firebase;

$config = new Configuration();
$config->setAuthConfigFile(__DIR__.'/auth/wavelength.json');

$firebase = new Firebase('https://wavelength-d78bb.firebaseio.com', $config);

$reference = $firebase->getReference('/');

$all = $reference->getData();

$username = 'root';
$password = 'stangetz';
$host = '127.0.0.1';
$db = 'analytics';
$connection = new PDO("mysql:dbname=$db;host=$host", $username, $password);

if ($connection) {
    foreach ($all as $name => $data){

        switch ($name){
            case 'activities':
                insertActivitiesAffiliations($connection,$name, $data);
                break;
            case 'affiliations':
                insertActivitiesAffiliations($connection,$name, $data);
                break;

            case 'user_profiles':
                insertProfiles($connection,$data);
                break;

        }
    }

}

function insertActivitiesAffiliations($connection,$tableName, $data){

    $query = 'DROP TABLE IF EXISTS ' . $tableName;
    $stmt = $connection->prepare($query);
    $stmt->execute();

    $query = <<<EOD
      CREATE TABLE IF NOT EXISTS $tableName (
        id STRING PRIMARY KEY,
        icon STRING,
        name STRING,
        uid STRING
        )
EOD;
    $stmt = $connection->prepare($query);
    $stmt->execute();

    $query = '';
    foreach ($data as $id => $fields){

        $fields['icon'] = SQLite3::escapeString($fields['icon']);
        $fields['name'] = SQLite3::escapeString($fields['name']);
        $fields['uid'] = SQLite3::escapeString($fields['uid']);

        $query.="INSERT INTO $tableName(id, icon, name, uid) VALUES('$id','{$fields['icon']}','{$fields['name']}','{$fields['uid']}'); \n";


    }

    echo $query;
    $stmt = $connection->prepare($query);
    $stmt->execute();

}



function insertProfiles($connection,$data){

    $query = 'DROP TABLE IF EXISTS user_profiles';
    $stmt = $connection->prepare($query);
    $stmt->execute();

    $query = 'DROP TABLE IF EXISTS user_activities';
    $stmt = $connection->prepare($query);
    $stmt->execute();

    $query = 'DROP TABLE IF EXISTS user_affiliations';
    $stmt = $connection->prepare($query);
    $stmt->execute();

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
    $stmt = $connection->prepare($query);
    $stmt->execute();

    $query = <<<EOD
      CREATE TABLE IF NOT EXISTS user_activities (
        uid VARCHAR(50),
        activity_id VARCHAR(50)
        )
EOD;
    $stmt = $connection->prepare($query);
    $stmt->execute();


    $query = <<<EOD
      CREATE TABLE IF NOT EXISTS user_affiliations (
        uid VARCHAR(50),
        affiliation_id VARCHAR(50)
        )
EOD;
    $stmt = $connection->prepare($query);
    $stmt->execute();

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

        $stmt = $connection->prepare($sql);
        $stmt->execute();

        $sql = '';
        foreach ($fields['affiliations'] as $id=>$item){

            $sql.="INSERT INTO user_affiliations(uid, affiliation_id) VALUES('$uid','{$id}'); \n";

        }
        echo $sql;
        $stmt = $connection->prepare($sql);
        $stmt->execute();
    }

    echo $query;
    $stmt = $connection->prepare($query);
    $stmt->execute();

}

echo 'Done';







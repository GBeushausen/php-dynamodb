<?php

require dirname(__DIR__) . '/vendor/autoload.php';

use Aws\DynamoDb\Marshaler;
use Guillermoandrae\Db\DynamoDb\AttributeTypes;
use Guillermoandrae\Db\DynamoDb\DynamoDbAdapter;
use Guillermoandrae\Db\DynamoDb\KeyTypes;
use Guillermoandrae\Db\DynamoDb\DynamoDbClient;

// create a new DynamoDB client
$dynamoDbClient = new DynamoDbClient();

// create a new Marshaler
$marshaler = new Marshaler();

// pass the client to the adapter
$adapter = new DynamoDbAdapter($dynamoDbClient, $marshaler);

try {
    // create the table
    $tableName = 'singers';
    $keys = [
        'name' => [
            'attributeType' => AttributeTypes::STRING,
            'keyType' => KeyTypes::HASH
        ],
        'year' => [
            'attributeType' => AttributeTypes::NUMBER,
            'keyType' => KeyTypes::RANGE
        ],
    ];
    $adapter->useTable($tableName)->createTable($keys);

    // insert items
    $names = ['Marvin Gaye', 'Jackie Wilson'];
    foreach ($names as $name) {
        $adapter->useTable($tableName)->insert([
            'name' => $name,
            'year' => 1984,
            'single' => 'Nightshift'
        ]);
        printf("Successfully added '%s' to the '%s' table!" . PHP_EOL, $name, $tableName);
    }

    // get all items
    $items = $adapter->useTable($tableName)->findAll();
    printf("The following items were found in the '%s' table:" . PHP_EOL, $tableName);
    foreach ($items as $item) {
        printf(
            "\t - '%s', who died in '%s'" . PHP_EOL,
            $item['name'],
            $item['year'],
            $tableName
        );
    }

    // get an item
    $item = $adapter->useTable($tableName)->findByPrimaryKey([
        'name' => 'Marvin Gaye',
        'year' => 1984
    ]);
    printf(
        "Successfully retrieved '%s' (mentioned in the Commodores' tribute single '%s') from the '%s' table!" . PHP_EOL,
        $item['name'],
        $item['single'],
        $tableName
    );

    // delete an item
    if ($adapter->useTable($tableName)->delete(['name' => 'Marvin Gaye', 'year' => 1984])) {
        printf(
            "Successfully deleted '%s' from the '%s' table!" . PHP_EOL,
            $item['name'],
            $tableName
        );
    }

    // get all items
    $items = $adapter->useTable($tableName)->findAll();
    printf("The following items remain in the '%s' table:" . PHP_EOL, $tableName);
    foreach ($items as $item) {
        printf(
            "\t - '%s', who died in '%s'" . PHP_EOL,
            $item['name'],
            $item['year'],
            $tableName
        );
    }

    // delete the table
    $adapter->useTable($tableName)->deleteTable();

} catch (\Exception $ex) {
    die($ex->getMessage());
}
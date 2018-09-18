<?php

/**
 * Class Category
 * Model for post category.
 * Categories are stored in DB table 'categories'.
 * Categories can't be edited from UI.
 */
class Category {

    /**
     * @var int $id ID if category in 'id' column in DB table.
     */
    /**
     * @var string $name Name of category in 'name' column in DB table.
     */
    public $id, $name;

    /**
     * Category constructor.
     * @param $id
     * @param $name
     */
    public function __construct($id, $name) {
        $this->id       = $id;
        $this->name     = $name;
    }

    /**
     * Get all categories from DB table 'categories' and return them in an array.
     * @return array
     */
    public static function all() {
        $list = [];
        $db = Db::getInstance();
        $req = $db->query('SELECT * FROM categories');

        // we create a list of Category objects from the database results
        foreach($req->fetchAll() as $category) {
            $list[] = new Category($category['id'], $category['name']);
        }

        return $list;
    }
}

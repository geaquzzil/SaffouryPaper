<?php

namespace Etq\Restful\Actions;

use Monolog\Logger;

class Mapper extends  BaseMapper
{

    /**
     * Fetch all authors
     *
     * @return [Author]
     */
    public function fetchAll(string $tableName, ?Options $options = null): mixed
    {
        $sql = "SELECT * FROM author ORDER BY name ASC";
        $stmt = $this->db->query($sql);

        $results = [];
        while ($row = $stmt->fetch()) {
            $results[] = new Author($row);
        }

        return $results;
    }

    /**
     * Load a single author
     *
     * @return Author|false
     */
    public function view(string $tableName, $id): mixed
    {
        $sql = "SELECT * FROM author WHERE author_id = :author_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['author_id' => $id]);
        $data = $stmt->fetch();

        if ($data) {
            return new Author($data);
        }

        return false;
    }

    /**
     * Create an author
     *
     * @return Author
     */
    public function insert(string $tableName, $object): mixed
    {
        $data = $author->getArrayCopy();
        $data['created'] = date('Y-m-d H:i:s');
        $data['updated'] = $data['created'];

        $query = "INSERT INTO author (author_id, name, biography, date_of_birth, created, updated)
            VALUES (:author_id, :name, :biography, :date_of_birth, :created, :updated)";
        $stmt = $this->db->prepare($query);
        $result = $stmt->execute($data);

        return new Author($data);
    }

    /**
     * Update an author
     *
     * @return Author
     */
    public function update(string $tableName, $objcet): mixed
    {
        $data = $author->getArrayCopy();
        $data['updated'] = date('Y-m-d H:i:s');

        $query = "UPDATE author
            SET name = :name,
                biography = :biography,
                date_of_birth = :date_of_birth,
                created = :created,
                updated = :updated
            WHERE author_id = :author_id
            ";

        $stmt = $this->db->prepare($query);
        $result = $stmt->execute($data);

        return new Author($data);
    }

    /**
     * Delete an author
     *
     * @param $object       Id of author to delete
     * @return boolean  True if there was an author to delete
     */
    public function delete(string $tableName, $object): mixed
    {
        $data['author_id'] = $id;
        $query = "DELETE FROM author WHERE author_id = :author_id";

        $stmt = $this->db->prepare($query);
        $stmt->execute($data);

        return (bool)$stmt->rowCount();
    }
}

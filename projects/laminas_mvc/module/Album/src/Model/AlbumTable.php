<?php

namespace Album\Model;

use RuntimeException;
use Laminas\Db\TableGateway\TableGatewayInterface;
use Laminas\Stdlib\DispatchableInterface;

class AlbumTable implements DispatchableInterface
{
    private $tableGateway;

    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function dispatch(\Laminas\Stdlib\RequestInterface $request, \Laminas\Stdlib\ResponseInterface $response = null)
    {
        // Implementation of the dispatch method
    }

    public function fetchAll()
    {
        return $this->tableGateway->select();
    }

    public function getAlbum($id)
    {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(['id' => $id]);
        if ($rowset instanceof \Laminas\Db\ResultSet\ResultSet) {
            $row = $rowset->current();
        } else {
            $row = null;
        }
        if (! $row) {
            throw new RuntimeException(sprintf(
                'Could not find row with identifier %d',
                $id
            ));
        }

        return $row;
    }

    public function saveAlbum(Album $album)
    {
        $data = [
            'artist' => $album->getArtist(),
            'title'  => $album->getTitle(),
        ];

        $id = (int) $album->getId();

        if ($id === 0) {
            $this->tableGateway->insert($data);
            return;
        }

        try {
            $this->getAlbum($id);
        } catch (RuntimeException $e) {
            throw new RuntimeException(sprintf(
                'Cannot update album with identifier %d; does not exist',
                $id
            ));
        }

        $this->tableGateway->update($data, ['id' => $id]);
    }

    public function deleteAlbum($id)
    {
        $this->tableGateway->delete(['id' => (int) $id]);
    }
    
}

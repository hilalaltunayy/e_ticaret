<?php

namespace App\Models;

class DashboardBlockModel extends DashboardBlockTypeModel
{
    public function getActiveBlocks(): array
    {
        return $this->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();
    }

    public function findActiveById(string $id): ?array
    {
        $row = $this->where('id', $id)
            ->where('is_active', 1)
            ->first();

        return is_array($row) ? $row : null;
    }
}

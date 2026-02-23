<?php

namespace App\Models;

class VisitModel extends BaseUuidModel
{
    protected $table = 'visits';
    protected $returnType = 'array';
    protected $allowedFields = ['id', 'visited_at'];
    protected $useSoftDeletes = false;
    protected $useTimestamps = false;

    public function countBetween(string $start, string $end): int
    {
        return (int) $this->builder()
            ->where('visited_at >=', $start)
            ->where('visited_at <=', $end)
            ->countAllResults();
    }

    public function getDailyCounts(string $start, string $end): array
    {
        return $this->builder()
            ->select('DATE(visited_at) as d, COUNT(*) as c')
            ->where('visited_at >=', $start)
            ->where('visited_at <=', $end)
            ->groupBy('DATE(visited_at)')
            ->orderBy('d', 'ASC')
            ->get()
            ->getResultArray();
    }
}

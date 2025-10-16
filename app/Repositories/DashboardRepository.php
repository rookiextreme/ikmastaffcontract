<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

class DashboardRepository
{
    public function statesOnlyId($states){
        $ids = [];
        foreach ($states as $state){
            $ids[] = $state->id;
        }

        return $ids;
    }

    public function statesOnlyName($states){
        $names = [];
        foreach ($states as $state){
            $names[] = $state->name;
        }

        return $names;
    }

    public function getStaffCountByState($states){
        $model = DB::select('
        SELECT
            COUNT(st.id) AS staff_count
            FROM states s
            LEFT JOIN staffs st ON s.id = st.state_id
            WHERE s.id IN ('.implode(',', array_fill(0, count($states), '?')).')
            GROUP BY s.name
        ', $states);

        $countOnly = [];
        foreach ($model as $item){
            $countOnly[] = $item->staff_count;
        }
        return $countOnly;
    }

    public function getStaffLeaveCategoryCount($year){
        $model = DB::select('
            SELECT
            lc.id,
            COUNT(sle.id) AS entry_counts
            FROM leave_categories lc
            JOIN staff_leave_entries sle ON sle.leave_category_id = lc.id
            WHERE sle.leave_category_id IN (1,3,2)
            AND year(sle.created_at) = ?
            GROUP BY sle.leave_category_id
        ', [
            $year
        ]);

        $countOnly = [];
        foreach ($model as $item){
            $countOnly[] = $item->entry_counts;
        }

        return $countOnly;
    }

    public function academicsOnlyName($academics){
        $names = [];
        foreach ($academics as $academic){
            $names[] = $academic->name;
        }

        return $names;
    }

    public function getStaffByAcademic($year){
        $model = DB::select('
            SELECT
            aq.id,
            aq.name,
            COUNT(sa.id) AS academic_counts
            FROM academic_qualifications aq
            LEFT JOIN staff_academics sa ON sa.academic_qualification_id = aq.id
            LEFT JOIN staffs st ON st.id = sa.staff_id
            AND YEAR(st.created_at) = ?
            GROUP BY aq.id,aq.name
        ', [
            $year
        ]);

        $countOnly = [];
        foreach ($model as $item){
            $countOnly[] = $item->academic_counts;
        }

        return $countOnly;
    }
}

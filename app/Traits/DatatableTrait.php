<?php

namespace App\Traits;

/**
 * DatatableTrait — Reusable server-side DataTables handler untuk CI4.
 *
 * Cara pakai di Controller:
 *   use App\Traits\DatatableTrait;
 *   class FooController extends BaseController {
 *       use DatatableTrait;
 *       public function getData() {
 *           ['draw'=>$draw,'start'=>$start,'length'=>$length,'search'=>$search,'order'=>$order] = $this->dtRequest();
 *           // ... build query, count, format rows ...
 *           return $this->dtResponse($draw, $total, $filtered, $rows);
 *       }
 *   }
 */
trait DatatableTrait
{
    /**
     * Parse DataTables AJAX request parameters.
     */
    protected function dtRequest(): array
    {
        $req = $this->request;
        return [
            'draw'   => (int)  ($req->getPost('draw')                  ?? 1),
            'start'  => (int)  ($req->getPost('start')                 ?? 0),
            'length' => (int)  ($req->getPost('length')                ?? 10),
            'search' => trim(   $req->getPost('search')['value']       ?? ''),
            'order'  =>         $req->getPost('order')                 ?? [],
        ];
    }

    /**
     * Format DataTables JSON response.
     */
    protected function dtResponse(int $draw, int $total, int $filtered, array $data): \CodeIgniter\HTTP\Response
    {
        return $this->response->setJSON([
            'draw'            => $draw,
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $data,
        ]);
    }

    /**
     * Resolve ORDER BY column + direction from DT order params.
     *
     * @param array  $order   DT order param (e.g. [['column'=>1,'dir'=>'asc']])
     * @param array  $colMap  Map of DT column index → DB column expression
     * @param string $default Default column if order not resolved
     */
    protected function dtOrder(array $order, array $colMap, string $default = ''): array
    {
        $idx = (int) ($order[0]['column'] ?? 0);
        $dir = ($order[0]['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
        $col = $colMap[$idx] ?? ($default ?: reset($colMap));
        return [$col, $dir];
    }

    /**
     * Generate action buttons HTML (edit + delete) — pakai di dalam getData() rowFormatter.
     */
    protected function dtActions(array $buttons): string
    {
        $html = '<div style="display:flex;gap:4px;flex-wrap:wrap">';
        foreach ($buttons as $btn) {
            if (!($btn['show'] ?? true)) continue;
            $type    = $btn['type']    ?? 'secondary';
            $icon    = $btn['icon']    ?? 'fa-pen';
            $title   = $btn['title']   ?? '';
            $href    = $btn['href']    ?? '#';
            $extra   = $btn['extra']   ?? '';
            $isAjax  = $btn['ajax']    ?? false;

            if ($isAjax) {
                $html .= "<button class=\"btn btn-sm btn-{$type} btn-delete\"
                    data-url=\"{$href}\" title=\"{$title}\" {$extra}>
                    <i class=\"fas {$icon}\"></i></button>";
            } else {
                $html .= "<a href=\"{$href}\" class=\"btn btn-sm btn-{$type}\"
                    title=\"{$title}\" {$extra}>
                    <i class=\"fas {$icon}\"></i></a>";
            }
        }
        $html .= '</div>';
        return $html;
    }
}

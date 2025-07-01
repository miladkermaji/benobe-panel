<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * گرفتن لیست منوهای سفارشی
     *
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "پزشکان من",
     *       "url": "/my-doctors",
     *       "icon": "doctors.svg",
     *       "position": "top",
     *       "order": 1,
     *       "children": []
     *     }
     *   ]
     * }
     * @response 401 {
     *   "status": "error",
     *   "message": "توکن نامعتبر است",
     *   "data": null
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "خطای سرور",
     *   "data": null
     * }
     */
    public function getCustomMenus(Request $request)
    {
        // گرفتن منوهای فعال سطح بالا (parent_id = null)
        $menus = Menu::where('status', 1)
            ->whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->select('id', 'parent_id', 'name', 'url', 'icon', 'position', 'order');
            }])
            ->select('id', 'name', 'url', 'icon', 'position', 'order')
            ->orderBy('order')
            ->get();

// فرمت کردن داده‌ها
        $formattedMenus = $menus->map(function ($menu) {
            return [
                'id'       => $menu->id,
                'name'     => $menu->name,
                'url'      => $menu->url,
                'icon'     => $menu->icon,
                'position' => $menu->position,
                'order'    => $menu->order,
                'children' => $menu->children->map(function ($child) {
                    return [
                        'id'       => $child->id,
                        'name'     => $child->name,
                        'url'      => $child->url,
                        'icon'     => $child->icon,
                        'position' => $child->position,
                        'order'    => $child->order,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'status' => 'success',
            'data'   => $formattedMenus,
        ], 200);

    }
}

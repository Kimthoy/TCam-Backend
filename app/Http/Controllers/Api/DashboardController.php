<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\{
    Post,
    Product,
    Service,
    Banner,
    ContactMessage,
    Customer,
    Partner
};

class DashboardController extends Controller
{
    public function stats()
    {
        $stats = [
            [
                'title' => 'Total Products',
                'value' => Product::where('is_published', 1)->count(),
                'change' => '+12%',
                'trend' => 'up',
                'variant' => 'teal',
                'icon' => 'Package2',
                'subtitle' => 'Published',
                'trendData' => $this->getLast7DaysCount(Product::class),
            ],
            [
                'title' => 'Active Services',
                'value' => Service::where('is_published', 1)->count(),
                'change' => '+8%',
                'trend' => 'up',
                'variant' => 'purple',
                'icon' => 'Briefcase',
                'subtitle' => 'Live services',
                'trendData' => $this->getLast7DaysCount(Service::class),
            ],
            [
                'title' => 'New Messages',
                'value' => ContactMessage::where('handled', 0)->count(),
                'change' => '+45%',
                'trend' => 'up',
                'variant' => 'blue',
                'icon' => 'MessageSquare',
                'subtitle' => 'Unread',
                'trendData' => $this->getLast7DaysCount(ContactMessage::class),
            ],
            [
                'title' => 'Blog Posts',
                'value' => Post::where('is_active', 1)->count(),
                'change' => '+23%',
                'trend' => 'up',
                'variant' => 'orange',
                'icon' => 'FileText',
                'subtitle' => 'Published',
                'trendData' => $this->getLast7DaysCount(Post::class),
            ],
            [
                'title' => 'Active Banners',
                'value' => Banner::where('status', 1)->count(),
                'change' => '+5%',
                'trend' => 'up',
                'variant' => 'pink',
                'icon' => 'Image',
                'subtitle' => 'Currently shown',
                'trendData' => [],
            ],
            [
                'title' => 'Total Clients',
                'value' => Customer::where('is_active', 1)->count(),
                'change' => '+18%',
                'trend' => 'up',
                'variant' => 'green',
                'icon' => 'Users',
                'subtitle' => 'All time',
                'trendData' => [],
            ],
        ];

        return response()->json($stats);
    }

    public function activity()
    {
        $last7Days = collect(range(6, 0))->map(fn($i) => Carbon::today()->subDays($i));

        $data = $last7Days->map(function ($date) {
            return ContactMessage::whereDate('created_at', $date)->count()
                + Post::whereDate('created_at', $date)->count();
        })->values()->all();

        return response()->json([
            'labels' => $last7Days->map(fn($d) => $d->format('M d'))->toArray(),
            'points' => $data,
        ]);
    }

    private function getLast7DaysCount($model)
    {
        return collect(range(6, 0))->map(function ($i) use ($model) {
            $date = Carbon::today()->subDays($i);
            return $model::whereDate('created_at', $date)->count();
        })->take(7)->values()->all();
    }
}

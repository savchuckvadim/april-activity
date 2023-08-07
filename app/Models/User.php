<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    protected $fillable = [
        'name',
        'surname',
        'email',
        'password',
        'role_id',
        'photo'
    ];
    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getAvatarUrl()
    {
        $hash = md5($this->email);
        $url = "https://www.gravatar.com/avatar/" . $hash . "?d=robohash";
        return $url;
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    public function offers()
    {
        return $this->belongsToMany(Offer::class, 'offer_masters', 'master_id', 'offer_id');
    }

    public function mastersLinks() //offers на которые подписался мастер
    {
        return $this->hasMany(Link::class, 'master_id');
    }

    public function advertisersLinks() //offers на которые подписался мастер
    {
        return $this->hasMany(Link::class, 'advertiser_id');
    }

    //followedOffers
    //createdOffers

    public function createdOffers()
    {
        return $this->hasMany(Offer::class, 'advertiser_id');
    }


    public static function mastersFinance($date)
    {
        $items = [];
        $user = Auth::user();
    
        $totalTransitions = 0;
        $totalProfit = 0;
        $profit = 0;
        $links = $user->mastersLinks;
       
        if ($date) {
            if ($date == 1) {
                $links = Link::where('master_id', $user->id)->whereDay('updated_at', now()->day)->get();
            } else if ($date == 2) {
                $links = Link::where('master_id', $user->id)->whereMonth('updated_at', now()->month)->get();
            } else if ($date == 3) {
                $links = Link::where('master_id', $user->id)->whereYear('updated_at', now()->year)->get();
            }
        } 



        foreach ($links as $link) {
            $offer = $link->offer;
            $transitions = $link->transitions;

            if ($transitions == 0) {
                $isFollowing = $offer->followers->find($user->id);
                if (!$isFollowing) {
                    continue;
                }
            }

            $totalTransitions += $transitions;


            $profitFromLinkTransitions = $transitions * $offer->mastersProfit;
            $profit += $offer->mastersProfit;
            $totalProfit += $profitFromLinkTransitions;

            $financeItem = [
                'name' => $offer->name,
                'transitions' => $transitions,
                'price' => round($offer->mastersProfit, 2),
                'profit' => round($profitFromLinkTransitions, 2),
                'activity' => $link->updated_at
            ];
            array_push($items, $financeItem);
        }
        $total = [
            'totalLinks' => 'Total: ' . count($items),
            'transitions' => $totalTransitions,
            'profit' => round($profit, 2),
            'totalProfit' => round($totalProfit, 2),
            'created_at' => null

        ];
        $finance = [
            'items' => $items,
            'total' => $total

        ];
        return $finance;
    }
    public static function advertFinance($date)
    {
        $items = [];
        $user = Auth::user();
        $offers = $user->createdOffers;
        if ($date) {
            if ($date == 1) {
                $offers = Offer::where('advertiser_id', $user->id)->whereDay('updated_at', now()->day)->get();
            } else if ($date == 2) {
                $offers = Offer::where('advertiser_id', $user->id)->whereMonth('updated_at', now()->month)->get();
            } else if ($date == 3) {
                $offers = Offer::where('advertiser_id', $user->id)->whereYear('updated_at', now()->year)->get();
            }
        }
        $offersCount = $offers->count();

        $totalMasters = 0;
        $totalTransitions = 0;
        $totalExpenses = 0;

        foreach ($offers as $offer) {
            $transitions = 0;
            $transitions = 0;
            $expenses = 0;
            $masters = $offer->followers->count();
            // if ($masters) {
                if ($offer->transitions()['transitions']) {
                    $transitions = $offer->transitions()['transitions'];
                    $expenses = $transitions * $offer->price;
                }
            // }



            $totalMasters += $masters;
            $totalTransitions += $transitions;
            $totalExpenses += $expenses;
            $item = [
                'offer' => $offer->name,
                'created' => $offer->created_at,
                'followers' => $masters,
                'transitions' => $transitions,
                'price' => round($offer->price, 2),
                'expenses' => round($expenses, 2)
            ];
            array_push($items, $item);
        }
        $total = [
            'offers' => 'Total Offers: ' . $offersCount,
            'created' => '',
            'followers' => $totalMasters,
            'transitions' => $totalTransitions,
            'price' => null,
            'expenses' => $totalExpenses,
        ];
        $finance = [
            'items' => array_reverse($items),
            'total' => $total

        ];
        return $finance;
    }
    public static function adminsFinance($date)
    {
        $items = [];
        $links = Link::all();
        $linksCount = $links->count();
        if ($date) {
            if ($date == 1) {
                $links = Link::whereDay('created_at', now()->day)->get();
            } else if ($date == 2) {
                $links = Link::whereMonth('created_at', now()->month)->get();
            } else if ($date == 3) {
                $links = Link::whereYear('created_at', now()->year)->get();
            }
        }
        $totalPrice = 0;
        $totalTransitions = 0;
        $totalFailTransitions = 0;
        $totalProfit = 0;

        foreach ($links as $link) {

            $transitions = $link->transitions;
            $failTransitions = $link->fail_transitions;
            $price = $link->offer->appsProfit;
            $profit =  $price * $transitions;

            $totalTransitions +=  $transitions;
            $totalFailTransitions += $failTransitions;
            $totalPrice += $price;
            $totalProfit += $profit;

            $item = [
                'created' => $link->created_at,
                'link' => $link->url,
                'offerName' => $link->offer->name,     
                'price' => round($price, 2),
                'transitions' => $transitions,
                'fails' => $failTransitions,
                'profit' => round($profit, 2)
            ];

            array_push($items, $item);
        }

        $total = [
            'created' => '',
            'link' => 'Total Links: '.$linksCount,
            'offer' => '',           
            'price' => round($totalPrice, 2),
            'transitions' => $totalTransitions,
            'fail_transitions' =>  $totalFailTransitions,
            'profit' => round($totalProfit, 2)
        ];
        $finance = [
            'items' => $items,
            'total' => $total

        ];
        return $finance;
    }

    protected static function booted()
    {
        static::created(function ($user) {
            $user->photo = $user->getAvatarUrl();
            $user->save();
            // $profile = new Profile;
            // $profile->user_id = $user->id;
            // $profile->name = $user->name;
            // $profile->surname = $user->surname;
            // $profile->email = $user->email;

            // $profile->save();
            // return $profile;
        });
    }
}

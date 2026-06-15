<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\MenuItem;
use App\Models\Restaurant;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    private function img(string $seed, int $w = 800, int $h = 600): string
    {
        return "https://picsum.photos/seed/{$seed}/{$w}/{$h}";
    }

    private function avatar(string $seed): string
    {
        return 'https://i.pravatar.cc/200?u='.urlencode($seed);
    }

    public function run(): void
    {
        $week = collect(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'])
            ->map(fn ($d) => ['day' => $d, 'open' => '09:00', 'close' => '22:00'])->all();

        // ---- Categories ----
        foreach ([
            ['fast-food', 'Fast Food', 'fast-food', '#FFE7CC'],
            ['local-dishes', 'Local Dishes', 'restaurant', '#E8F1EC'],
            ['bbq-grill', 'BBQ / Grill', 'flame', '#FFE0DC'],
            ['pizza', 'Pizza', 'pizza', '#FFF3CC'],
            ['drinks', 'Drinks', 'wine', '#E0F0FF'],
            ['desserts', 'Desserts', 'ice-cream', '#FBE4F3'],
        ] as [$slug, $name, $icon, $color]) {
            Category::create(compact('slug', 'name', 'icon', 'color'));
        }

        // ---- Demo accounts (password = "password") ----
        User::create(['name' => 'Platform Admin', 'email' => 'admin@hyperlocal.test', 'password' => 'password', 'role' => 'admin']);
        User::create(['name' => 'Jerry Adams', 'email' => 'jerry@example.com', 'password' => 'password', 'role' => 'customer', 'phone' => '+1 555 0100', 'avatar' => $this->avatar('jerry-user')]);
        User::create(['name' => 'Daniel Okoro', 'email' => 'rider1@hyperlocal.test', 'password' => 'password', 'role' => 'rider', 'phone' => '+1 555 0142', 'avatar' => $this->avatar('daniel-rider'), 'is_available' => true]);
        User::create(['name' => 'Sophia Bello', 'email' => 'rider2@hyperlocal.test', 'password' => 'password', 'role' => 'rider', 'phone' => '+1 555 0188', 'avatar' => $this->avatar('sophia-rider'), 'is_available' => true]);

        // ---- Restaurants (mirrors the mobile app's mock data) ----
        $restaurants = [
            ['Smash & Grill House', 'smashgrill', ['Burgers', 'American', 'Fast Food'], ['fast-food', 'bbq-grill'], 4.8, 1243, 0.8, 25, 1.99, 8, true, true, '20% off orders over $25', 2, '12 Market Street, Downtown'],
            ["Mama Ada's Kitchen", 'mamaada', ['Local Dishes', 'African', 'Rice'], ['local-dishes'], 4.6, 870, 1.4, 35, 0, 10, true, true, 'Free delivery today', 1, '4 Heritage Close, Old Town'],
            ['Napoli Wood-Fired Pizza', 'napoli', ['Pizza', 'Italian'], ['pizza'], 4.7, 1560, 2.1, 30, 2.49, 12, true, false, null, 2, '88 Vine Avenue, Riverside'],
            ['Ember BBQ Pit', 'ember', ['BBQ', 'Grill', 'Ribs'], ['bbq-grill'], 4.5, 642, 3.0, 45, 3.0, 15, false, false, null, 3, '21 Smoke Lane, Eastside'],
            ['Sweet Tooth Desserts', 'sweettooth', ['Desserts', 'Cakes', 'Ice Cream'], ['desserts', 'drinks'], 4.9, 2110, 1.0, 20, 1.5, 6, true, true, 'Buy 1 get 1 on cupcakes', 2, '5 Sugar Plaza, Downtown'],
            ['Fresh Sip Juice Bar', 'freshsip', ['Drinks', 'Smoothies', 'Juices'], ['drinks'], 4.4, 389, 0.5, 15, 0, 5, true, false, null, 1, '2 Greenway, Downtown'],
            ['Crispy Cluck Fried Chicken', 'crispycluck', ['Fast Food', 'Chicken'], ['fast-food'], 4.3, 998, 2.7, 40, 2.0, 9, true, true, 'Family bucket deal', 2, '17 Poultry Road, Northgate'],
            ['Heritage Jollof Spot', 'heritage', ['Local Dishes', 'Jollof', 'Grill'], ['local-dishes', 'bbq-grill'], 4.7, 1320, 1.8, 33, 1.0, 10, true, false, null, 2, '9 Festival Street, Old Town'],
        ];

        foreach ($restaurants as $i => $r) {
            $idx = $i + 1;
            $owner = User::create([
                'name' => $r[0].' Manager',
                'email' => "rest{$idx}@hyperlocal.test",
                'password' => 'password',
                'role' => 'restaurant',
            ]);
            Restaurant::create([
                'user_id' => $owner->id,
                'name' => $r[0],
                'cover_image' => $this->img($r[1].'-cover'),
                'logo' => $this->img($r[1].'-logo', 200, 200),
                'cuisines' => $r[2],
                'categories' => $r[3],
                'rating' => $r[4],
                'review_count' => $r[5],
                'distance_km' => $r[6],
                'eta_minutes' => $r[7],
                'delivery_fee' => $r[8],
                'min_order' => $r[9],
                'is_open' => $r[10],
                'has_promotion' => $r[11],
                'promotion_text' => $r[12],
                'price_level' => $r[13],
                'address' => $r[14],
                'opening_hours' => $week,
            ]);
        }

        // ---- Reusable customization option groups ----
        $addOns = ['id' => 'add-ons', 'title' => 'Add-ons', 'multiple' => true, 'required' => false, 'choices' => [
            ['id' => 'extra-cheese', 'label' => 'Extra Cheese', 'price' => 1.0],
            ['id' => 'extra-sauce', 'label' => 'Extra Sauce', 'price' => 0.5],
            ['id' => 'extra-bacon', 'label' => 'Extra Bacon', 'price' => 1.5],
            ['id' => 'extra-patty', 'label' => 'Extra Patty', 'price' => 2.5],
        ]];
        $removals = ['id' => 'removals', 'title' => 'Remove', 'multiple' => true, 'required' => false, 'choices' => [
            ['id' => 'no-onions', 'label' => 'No Onions', 'price' => 0],
            ['id' => 'no-mayo', 'label' => 'No Mayo', 'price' => 0],
            ['id' => 'no-pickles', 'label' => 'No Pickles', 'price' => 0],
        ]];
        $size = ['id' => 'size', 'title' => 'Choose a size', 'multiple' => false, 'required' => true, 'choices' => [
            ['id' => 'size-regular', 'label' => 'Regular', 'price' => 0],
            ['id' => 'size-large', 'label' => 'Large', 'price' => 2.0],
            ['id' => 'size-xl', 'label' => 'Extra Large', 'price' => 3.5],
        ]];
        $protein = ['id' => 'protein', 'title' => 'Choose protein', 'multiple' => false, 'required' => true, 'choices' => [
            ['id' => 'p-chicken', 'label' => 'Grilled Chicken', 'price' => 0],
            ['id' => 'p-beef', 'label' => 'Beef', 'price' => 1.0],
            ['id' => 'p-fish', 'label' => 'Fried Fish', 'price' => 1.5],
        ]];

        // ---- Menu items (restaurant_id = index) ----
        $menu = [
            [1, 'Classic Smash Burger', 'Double smashed beef patties, cheddar, house sauce and pickles.', 9.5, 'classic-smash', 'Lunch', ['Beef patty', 'Cheddar', 'House sauce', 'Pickles'], 15, 720, true, [$addOns, $removals]],
            [1, 'Bacon BBQ Burger', 'Juicy beef, crispy bacon, smoky BBQ sauce and onion rings.', 11.0, 'bacon-bbq', 'Dinner', ['Beef patty', 'Bacon', 'BBQ sauce'], 18, 880, true, [$addOns, $removals]],
            [1, 'Loaded Fries', 'Crispy fries topped with cheese sauce, jalapeños and chives.', 5.5, 'loaded-fries', 'Lunch', ['Potato', 'Cheese sauce', 'Jalapeños'], 10, null, false, [$addOns]],
            [1, 'Chocolate Milkshake', 'Thick and creamy chocolate milkshake.', 4.5, 'choc-shake', 'Drinks', ['Milk', 'Chocolate', 'Ice cream'], 5, null, false, [$size]],
            [2, 'Jollof Rice & Chicken', 'Smoky party-style jollof rice with grilled chicken and plantain.', 8.0, 'jollof-chicken', 'Lunch', ['Rice', 'Chicken', 'Plantain'], 20, 650, true, [$protein]],
            [2, 'Egusi Soup & Pounded Yam', 'Rich melon-seed soup with assorted meat and pounded yam.', 9.5, 'egusi', 'Dinner', ['Egusi', 'Spinach', 'Yam'], 25, null, false, []],
            [2, 'Akara & Pap', 'Golden bean fritters with smooth fermented corn pap.', 4.0, 'akara', 'Breakfast', ['Beans', 'Onion', 'Corn pap'], 12, null, false, []],
            [3, 'Margherita Pizza', 'San Marzano tomato, fresh mozzarella and basil.', 10.0, 'margherita', 'Dinner', ['Tomato', 'Mozzarella', 'Basil'], 16, null, true, [$size, $addOns]],
            [3, 'Pepperoni Pizza', 'Spicy pepperoni and extra mozzarella on a wood-fired base.', 12.5, 'pepperoni', 'Dinner', ['Tomato', 'Mozzarella', 'Pepperoni'], 16, null, true, [$size, $addOns]],
            [3, 'Garlic Bread', 'Wood-fired flatbread with garlic butter and herbs.', 4.5, 'garlic-bread', 'Lunch', ['Dough', 'Garlic', 'Butter'], 8, null, false, []],
            [4, 'Smoked Beef Ribs', 'Slow-smoked beef ribs glazed with signature BBQ sauce.', 16.0, 'beef-ribs', 'Dinner', ['Beef ribs', 'BBQ rub'], 30, null, true, [$addOns]],
            [4, 'Grilled Chicken Platter', 'Half chicken grilled over charcoal with coleslaw and corn.', 13.0, 'chicken-platter', 'Dinner', ['Chicken', 'Coleslaw', 'Corn'], 28, null, false, []],
            [5, 'Red Velvet Cake Slice', 'Moist red velvet with cream cheese frosting.', 5.0, 'red-velvet', 'Desserts', ['Cocoa', 'Cream cheese'], 3, null, true, []],
            [5, 'Vanilla Ice Cream Sundae', 'Vanilla bean ice cream with chocolate sauce and sprinkles.', 4.0, 'sundae', 'Desserts', ['Ice cream', 'Chocolate sauce'], 4, null, false, [$size]],
            [6, 'Tropical Smoothie', 'Mango, pineapple and banana blended with coconut water.', 5.5, 'tropical-smoothie', 'Drinks', ['Mango', 'Pineapple', 'Banana'], 6, null, true, [$size]],
            [6, 'Green Detox Juice', 'Spinach, cucumber, green apple and ginger cold-pressed juice.', 6.0, 'green-juice', 'Drinks', ['Spinach', 'Cucumber', 'Apple'], 6, null, false, [$size]],
            [7, 'Crispy Chicken Bucket', '8 pieces of crunchy fried chicken with a choice of dips.', 14.0, 'chicken-bucket', 'Dinner', ['Chicken', 'Spices'], 22, null, true, [$addOns]],
            [7, 'Spicy Chicken Sandwich', 'Crispy chicken fillet, spicy mayo and pickles in a soft bun.', 7.5, 'chicken-sandwich', 'Lunch', ['Chicken fillet', 'Spicy mayo'], 14, null, false, [$addOns, $removals]],
            [8, 'Smoky Party Jollof', 'Signature smoky jollof rice with grilled turkey and salad.', 9.0, 'party-jollof', 'Lunch', ['Rice', 'Turkey', 'Salad'], 21, null, true, []],
            [8, 'Suya Skewers', 'Spicy grilled beef skewers coated in groundnut spice mix.', 7.0, 'suya', 'Dinner', ['Beef', 'Suya spice', 'Onion'], 18, null, false, []],
        ];
        foreach ($menu as $m) {
            MenuItem::create([
                'restaurant_id' => $m[0],
                'name' => $m[1],
                'description' => $m[2],
                'price' => $m[3],
                'image' => $this->img($m[4]),
                'section' => $m[5],
                'ingredients' => $m[6],
                'prep_time_minutes' => $m[7],
                'calories' => $m[8],
                'popular' => $m[9],
                'option_groups' => $m[10],
            ]);
        }

        // ---- Coupons ----
        Coupon::create(['code' => 'WELCOME10', 'label' => '10% off your order', 'type' => 'percentage', 'value' => 10, 'min_subtotal' => 0]);
        Coupon::create(['code' => 'SAVE5', 'label' => '$5 off orders over $20', 'type' => 'fixed', 'value' => 5, 'min_subtotal' => 20]);
        Coupon::create(['code' => 'FREESHIP', 'label' => 'Free delivery', 'type' => 'fixed', 'value' => 0, 'min_subtotal' => 15]);

        // ---- A few reviews ----
        foreach ([
            [1, 'Jessica M.', 'jessica', 5, 'The smash burger was incredible — juicy and arrived hot.', ['Food Quality', 'Delivery']],
            [1, 'Marcus T.', 'marcus', 4, 'Great taste, but the fries could have been crispier.', ['Food Quality']],
            [2, 'David O.', 'david', 5, 'Authentic jollof, just like home. Generous portions.', ['Food Quality']],
            [3, 'Lucia P.', 'lucia', 4, 'Lovely thin-crust pizza. Delivery a little slow at peak.', ['Delivery', 'Food Quality']],
        ] as [$rid, $author, $seed, $rating, $comment, $tags]) {
            Review::create([
                'restaurant_id' => $rid,
                'author' => $author,
                'avatar' => $this->avatar($seed),
                'rating' => $rating,
                'comment' => $comment,
                'tags' => $tags,
                'photos' => [],
            ]);
        }
    }
}

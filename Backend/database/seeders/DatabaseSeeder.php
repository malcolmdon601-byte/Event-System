<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Equipment;
use App\Models\EquipmentReservation;
use App\Models\Event;
use App\Models\EventType;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Quotation;
use App\Models\Service;
use App\Models\Staff;
use App\Models\Task;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Demo login accounts (one per role) ----
        $admin = User::create([
            'name' => 'Amara Okoye', 'email' => 'admin@eventflow.test',
            'password' => Hash::make('password'), 'role' => 'super_admin', 'phone' => '+256700000001',
        ]);
        $manager = User::create([
            'name' => 'David Mwangi', 'email' => 'manager@eventflow.test',
            'password' => Hash::make('password'), 'role' => 'manager', 'phone' => '+256700000002',
        ]);
        $finance = User::create([
            'name' => 'Grace Nakato', 'email' => 'finance@eventflow.test',
            'password' => Hash::make('password'), 'role' => 'finance', 'phone' => '+256700000003',
        ]);
        $staffUser = User::create([
            'name' => 'Peter Ssemwogerere', 'email' => 'staff@eventflow.test',
            'password' => Hash::make('password'), 'role' => 'staff', 'phone' => '+256700000004',
        ]);
        $customerUser = User::create([
            'name' => 'Sarah Nabirye', 'email' => 'customer@eventflow.test',
            'password' => Hash::make('password'), 'role' => 'customer', 'phone' => '+256700000005',
        ]);

        // ---- Event types ----
        $types = collect(['Wedding', 'Corporate Event', 'Birthday Party', 'Conference', 'Anniversary'])
            ->map(fn ($name) => EventType::create(['name' => $name]));

        // ---- Services ----
        $services = collect([
            ['name' => 'Full Wedding Planning', 'category' => 'Planning', 'price' => 3500000, 'pricing_type' => 'fixed'],
            ['name' => 'Corporate Event Management', 'category' => 'Planning', 'price' => 2800000, 'pricing_type' => 'fixed'],
            ['name' => 'Décor & Styling', 'category' => 'Décor', 'price' => 1200000, 'pricing_type' => 'fixed'],
            ['name' => 'Catering (per guest)', 'category' => 'Catering', 'price' => 45000, 'pricing_type' => 'per_guest'],
            ['name' => 'Photography', 'category' => 'Media', 'price' => 900000, 'pricing_type' => 'fixed'],
            ['name' => 'Videography', 'category' => 'Media', 'price' => 1100000, 'pricing_type' => 'fixed'],
            ['name' => 'Live Band Entertainment', 'category' => 'Entertainment', 'price' => 1500000, 'pricing_type' => 'fixed'],
            ['name' => 'DJ & Sound System', 'category' => 'Entertainment', 'price' => 600000, 'pricing_type' => 'fixed'],
            ['name' => 'Tent & Marquee Hire', 'category' => 'Equipment', 'price' => 800000, 'pricing_type' => 'fixed'],
            ['name' => 'Chair & Table Hire (per guest)', 'category' => 'Equipment', 'price' => 8000, 'pricing_type' => 'per_guest'],
        ])->map(fn ($s) => Service::create($s + ['active' => true, 'description' => $s['name'].' delivered by our in-house team.']));

        // ---- Staff ----
        $staffMembers = collect([
            ['name' => 'Peter Ssemwogerere', 'role_title' => 'Event Coordinator', 'user_id' => $staffUser->id],
            ['name' => 'Joan Achieng', 'role_title' => 'Décor Lead'],
            ['name' => 'Brian Tumusiime', 'role_title' => 'Logistics Manager'],
            ['name' => 'Faith Auma', 'role_title' => 'Catering Supervisor'],
        ])->map(fn ($s) => Staff::create($s + ['status' => 'active']));

        // ---- Vendors ----
        $vendors = collect([
            ['name' => 'Kampala Fresh Catering', 'category' => 'Catering'],
            ['name' => 'Bright Lights Sound & Vision', 'category' => 'AV Equipment'],
            ['name' => 'Blossom Florals', 'category' => 'Décor'],
        ])->map(fn ($v) => Vendor::create($v + ['status' => 'active']));

        // ---- Equipment ----
        $chairs = Equipment::create(['name' => 'Banquet Chairs', 'category' => 'Furniture', 'total_quantity' => 300]);
        $tables = Equipment::create(['name' => 'Round Tables (10-seater)', 'category' => 'Furniture', 'total_quantity' => 30]);
        $tents = Equipment::create(['name' => 'Marquee Tents', 'category' => 'Structures', 'total_quantity' => 6]);
        $speakers = Equipment::create(['name' => 'PA Speaker Sets', 'category' => 'AV', 'total_quantity' => 10]);

        // ---- Customers ----
        $sarah = Customer::create([
            'user_id' => $customerUser->id, 'name' => 'Sarah Nabirye', 'email' => $customerUser->email,
            'phone' => '+256700000005', 'address' => 'Kololo, Kampala',
        ]);
        $james = Customer::create(['name' => 'James & Ritah Kato', 'email' => 'jkato@example.test', 'phone' => '+256701111111', 'address' => 'Ntinda, Kampala']);
        $techco = Customer::create(['name' => 'NovaTech Solutions Ltd', 'email' => 'events@novatech.test', 'phone' => '+256702222222', 'address' => 'Nakasero, Kampala']);
        $walkin = Customer::create(['name' => 'Michael Okot', 'email' => 'mokot@example.test', 'phone' => '+256703333333']);

        // ---- Event 1: Sarah's wedding — fully confirmed, deposit paid ----
        $wedding = Event::create([
            'customer_id' => $sarah->id, 'event_type_id' => $types[0]->id, 'name' => 'Sarah & Daniel\'s Wedding',
            'event_date' => now()->addDays(45)->toDateString(), 'start_time' => '14:00', 'end_time' => '22:00',
            'venue' => 'Sheraton Kampala Gardens', 'guest_count' => 220, 'budget' => 15000000,
            'status' => 'confirmed', 'notes' => 'Outdoor garden ceremony, indoor reception.', 'created_by' => $admin->id,
        ]);
        $wedding->services()->attach([
            $services[0]->id => ['quantity' => 1, 'unit_price' => $services[0]->price, 'subtotal' => $services[0]->price],
            $services[2]->id => ['quantity' => 1, 'unit_price' => $services[2]->price, 'subtotal' => $services[2]->price],
            $services[3]->id => ['quantity' => 220, 'unit_price' => $services[3]->price, 'subtotal' => 220 * $services[3]->price],
            $services[4]->id => ['quantity' => 1, 'unit_price' => $services[4]->price, 'subtotal' => $services[4]->price],
        ]);
        $wedding->staff()->attach([$staffMembers[0]->id, $staffMembers[1]->id]);
        $wedding->vendors()->attach([$vendors[0]->id => ['notes' => 'Confirmed menu tasting done.'], $vendors[2]->id => ['notes' => 'Floral mockups approved.']]);

        $weddingSubtotal = $services[0]->price + $services[2]->price + (220 * $services[3]->price) + $services[4]->price;
        $weddingQuote = Quotation::create([
            'quotation_number' => 'QT-WED0045', 'event_id' => $wedding->id, 'customer_id' => $sarah->id,
            'subtotal' => $weddingSubtotal, 'discount' => 500000, 'total' => $weddingSubtotal - 500000,
            'deposit_amount' => 5000000, 'balance' => $weddingSubtotal - 500000,
            'valid_until' => now()->addDays(10), 'status' => 'accepted', 'accepted_at' => now()->subDays(5),
            'notes' => 'Includes full wedding planning, décor, catering for 220 guests and photography.',
            'terms' => 'A 30% deposit confirms the booking. Balance due 14 days before the event.',
        ]);
        $weddingQuote->items()->createMany([
            ['service_id' => $services[0]->id, 'description' => $services[0]->name, 'quantity' => 1, 'unit_price' => $services[0]->price, 'subtotal' => $services[0]->price],
            ['service_id' => $services[2]->id, 'description' => $services[2]->name, 'quantity' => 1, 'unit_price' => $services[2]->price, 'subtotal' => $services[2]->price],
            ['service_id' => $services[3]->id, 'description' => $services[3]->name, 'quantity' => 220, 'unit_price' => $services[3]->price, 'subtotal' => 220 * $services[3]->price],
            ['service_id' => $services[4]->id, 'description' => $services[4]->name, 'quantity' => 1, 'unit_price' => $services[4]->price, 'subtotal' => $services[4]->price],
        ]);

        $weddingInvoice = Invoice::create([
            'invoice_number' => 'INV-WED0045', 'event_id' => $wedding->id, 'quotation_id' => $weddingQuote->id,
            'total' => $weddingQuote->total, 'amount_paid' => 5000000, 'balance' => $weddingQuote->total - 5000000,
            'status' => 'partial', 'due_date' => now()->addDays(10),
        ]);
        Payment::create([
            'payment_reference' => 'PMT-WED001', 'event_id' => $wedding->id, 'customer_id' => $sarah->id,
            'invoice_id' => $weddingInvoice->id, 'amount' => 5000000, 'method' => 'mobile_money',
            'status' => 'completed', 'is_simulated' => true, 'paid_at' => now()->subDays(5),
            'notes' => 'Deposit payment (simulated).', 'recorded_by' => $finance->id,
        ]);

        EquipmentReservation::create(['equipment_id' => $chairs->id, 'event_id' => $wedding->id, 'quantity' => 220, 'start_date' => $wedding->event_date, 'end_date' => $wedding->event_date]);
        EquipmentReservation::create(['equipment_id' => $tables->id, 'event_id' => $wedding->id, 'quantity' => 22, 'start_date' => $wedding->event_date, 'end_date' => $wedding->event_date]);
        EquipmentReservation::create(['equipment_id' => $tents->id, 'event_id' => $wedding->id, 'quantity' => 2, 'start_date' => $wedding->event_date, 'end_date' => $wedding->event_date]);

        Task::create(['event_id' => $wedding->id, 'title' => 'Confirm final guest count with caterer', 'assigned_staff_id' => $staffMembers[3]->id, 'due_date' => now()->addDays(20), 'priority' => 'high', 'status' => 'in_progress']);
        Task::create(['event_id' => $wedding->id, 'title' => 'Finalise floral arrangement mockups', 'assigned_staff_id' => $staffMembers[1]->id, 'due_date' => now()->addDays(15), 'priority' => 'medium', 'status' => 'todo']);
        Task::create(['event_id' => $wedding->id, 'title' => 'Site walkthrough at Sheraton Gardens', 'assigned_staff_id' => $staffMembers[0]->id, 'due_date' => now()->addDays(30), 'priority' => 'medium', 'status' => 'todo']);

        \App\Models\Message::create(['event_id' => $wedding->id, 'sender_id' => $admin->id, 'body' => 'Hi Sarah, your décor mockups are attached in the documents tab — let us know what you think!']);
        \App\Models\Message::create(['event_id' => $wedding->id, 'sender_id' => $customerUser->id, 'body' => 'These look beautiful! Can we swap the centerpiece flowers to white roses?']);

        // ---- Event 2: NovaTech corporate conference — quotation sent, awaiting acceptance ----
        $conf = Event::create([
            'customer_id' => $techco->id, 'event_type_id' => $types[3]->id, 'name' => 'NovaTech Annual Tech Summit',
            'event_date' => now()->addDays(70)->toDateString(), 'start_time' => '08:00', 'end_time' => '17:00',
            'venue' => 'Kampala Serena Conference Centre', 'guest_count' => 350, 'budget' => 20000000,
            'status' => 'quotation', 'created_by' => $manager->id,
        ]);
        $confQuote = Quotation::create([
            'quotation_number' => 'QT-CONF070', 'event_id' => $conf->id, 'customer_id' => $techco->id,
            'subtotal' => 9500000, 'discount' => 0, 'total' => 9500000, 'deposit_amount' => 3000000, 'balance' => 9500000,
            'valid_until' => now()->addDays(14), 'status' => 'sent',
            'notes' => 'Full-day conference package for 350 delegates.',
            'terms' => 'A 30% deposit confirms the booking. Balance due 14 days before the event.',
        ]);
        $confQuote->items()->createMany([
            ['service_id' => $services[1]->id, 'description' => $services[1]->name, 'quantity' => 1, 'unit_price' => $services[1]->price, 'subtotal' => $services[1]->price],
            ['service_id' => $services[7]->id, 'description' => $services[7]->name, 'quantity' => 1, 'unit_price' => $services[7]->price, 'subtotal' => $services[7]->price],
            ['service_id' => $services[9]->id, 'description' => $services[9]->name, 'quantity' => 350, 'unit_price' => $services[9]->price, 'subtotal' => 350 * $services[9]->price],
        ]);

        // ---- Event 3: James & Ritah's anniversary party — early-stage enquiry ----
        Event::create([
            'customer_id' => $james->id, 'event_type_id' => $types[4]->id, 'name' => '10th Anniversary Celebration',
            'event_date' => now()->addDays(90)->toDateString(), 'venue' => 'Private residence, Ntinda',
            'guest_count' => 60, 'budget' => 4000000, 'status' => 'enquiry',
            'notes' => 'Intimate garden celebration, considering a live band.', 'created_by' => $manager->id,
        ]);

        // ---- Event 4: Michael's birthday — completed, fully paid, for reporting history ----
        $bday = Event::create([
            'customer_id' => $walkin->id, 'event_type_id' => $types[2]->id, 'name' => 'Michael\'s 40th Birthday',
            'event_date' => now()->subDays(20)->toDateString(), 'venue' => 'Speke Resort Munyonyo',
            'guest_count' => 80, 'budget' => 5000000, 'status' => 'completed', 'created_by' => $admin->id,
        ]);
        $bdayInvoice = Invoice::create([
            'invoice_number' => 'INV-BDAY020', 'event_id' => $bday->id, 'total' => 4800000,
            'amount_paid' => 4800000, 'balance' => 0, 'status' => 'paid', 'due_date' => now()->subDays(25),
        ]);
        Payment::create([
            'payment_reference' => 'PMT-BDAY001', 'event_id' => $bday->id, 'customer_id' => $walkin->id,
            'invoice_id' => $bdayInvoice->id, 'amount' => 4800000, 'method' => 'bank_transfer',
            'status' => 'completed', 'is_simulated' => true, 'paid_at' => now()->subDays(22), 'recorded_by' => $finance->id,
        ]);

        $this->command->info('Demo data seeded: 5 users, 4 customers, 4 events across the full pipeline.');
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TravelFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'database.connections.sqlite.foreign_key_constraints' => false,
        ]);
        DB::purge();
        DB::setDefaultConnection('sqlite');

        Schema::create('kota_asal', function (Blueprint $table) {
            $table->increments('id_kota_asal');
            $table->string('nm_kota_asal');
        });
        Schema::create('kota_tujuan', function (Blueprint $table) {
            $table->increments('id_kota_tujuan');
            $table->string('nm_kota_tujuan');
        });
        Schema::create('kendaraan', function (Blueprint $table) {
            $table->increments('id_mobil');
            $table->string('jenis_mobil');
            $table->string('nomor_polisi');
        });
        Schema::create('member', function (Blueprint $table) {
            $table->increments('id_member');
            $table->string('nama');
        });
        Schema::create('jadwal', function (Blueprint $table) {
            $table->increments('id_jadwal');
            $table->unsignedInteger('id_kota_asal');
            $table->unsignedInteger('id_kota_tujuan');
            $table->unsignedInteger('harga');
            $table->date('tgl_berangkat');
            $table->time('jam_berangkat');
            $table->unsignedInteger('id_mobil');
        });
        Schema::create('pesan', function (Blueprint $table) {
            $table->increments('id_pesan');
            $table->string('nomor_kursi');
            $table->string('status');
            $table->unsignedInteger('id_jadwal');
            $table->unsignedInteger('id_member');
        });

        $migration = require base_path('database/migrations/2026_11_10_000000_create_pembayaran_table.php');
        $migration->up();

        DB::table('kota_asal')->insert(['id_kota_asal' => 1, 'nm_kota_asal' => 'Bandar Lampung']);
        DB::table('kota_tujuan')->insert(['id_kota_tujuan' => 1, 'nm_kota_tujuan' => 'Metro']);
        DB::table('kendaraan')->insert(['id_mobil' => 1, 'jenis_mobil' => 'Avanza', 'nomor_polisi' => 'BE 1234 AB']);
        DB::table('member')->insert(['id_member' => 1, 'nama' => 'Member Uji']);
        DB::table('jadwal')->insert([
            'id_jadwal' => 1,
            'id_kota_asal' => 1,
            'id_kota_tujuan' => 1,
            'harga' => 60000,
            'tgl_berangkat' => today()->addDays(2)->toDateString(),
            'jam_berangkat' => '08:00:00',
            'id_mobil' => 1,
        ]);
    }

    public function test_member_can_book_submit_payment_and_operator_can_confirm_it(): void
    {
        $memberSession = ['legacy_account' => ['id' => 1, 'role' => 'member']];

        $this->withSession($memberSession)
            ->post(route('transactions.store'), ['id_jadwal' => 1, 'nomor_kursi' => 2])
            ->assertRedirect(route('transactions.index'));

        $this->assertDatabaseHas('pesan', [
            'id_jadwal' => 1,
            'id_member' => 1,
            'nomor_kursi' => '2',
            'status' => 'Belum Bayar',
        ]);

        $this->withSession($memberSession)
            ->post(route('payments.store', 1), [
                'no_resi' => 'REF-TEST-1',
                'no_rek' => '1234567890123456',
                'tgl_transfer' => today()->toDateString(),
                'jam_transfer' => '09:30',
            ])
            ->assertRedirect(route('transactions.index'));

        $this->assertDatabaseHas('pesan', ['id_pesan' => 1, 'status' => 'Dalam Proses']);
        $this->assertDatabaseHas('pembayaran', ['id_pesan' => 1, 'status' => 'Dalam Proses']);

        $this->withSession(['legacy_account' => ['id' => 1, 'role' => 'operator_loket']])
            ->post(route('payments.confirm', 1))
            ->assertRedirect(route('payments.index'));

        $this->assertDatabaseHas('pesan', ['id_pesan' => 1, 'status' => 'Lunas']);
        $this->assertDatabaseHas('pembayaran', ['id_pembayaran' => 1, 'status' => 'Lunas']);
    }

    public function test_member_cannot_book_a_seat_twice(): void
    {
        DB::table('pesan')->insert([
            'id_pesan' => 1,
            'nomor_kursi' => '2',
            'status' => 'Lunas',
            'id_jadwal' => 1,
            'id_member' => 1,
        ]);

        $this->withSession(['legacy_account' => ['id' => 1, 'role' => 'member']])
            ->from(route('home'))
            ->post(route('transactions.store'), ['id_jadwal' => 1, 'nomor_kursi' => 2])
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors('nomor_kursi');
    }

    public function test_admin_can_create_a_new_schedule(): void
    {
        $this->withSession(['legacy_account' => ['id' => 1, 'role' => 'member']])
            ->post(route('schedules.store'), [
                'id_kota_asal' => 1,
                'id_kota_tujuan' => 1,
                'harga' => 70000,
                'tgl_berangkat' => today()->addDays(4)->toDateString(),
                'jam_berangkat' => '10:15',
                'id_mobil' => 1,
            ])
            ->assertForbidden();

        DB::table('kota_tujuan')->insert(['id_kota_tujuan' => 2, 'nm_kota_tujuan' => 'Pringsewu']);

        $this->withSession(['legacy_account' => ['id' => 1, 'role' => 'admin']])
            ->post(route('schedules.store'), [
                'id_kota_asal' => 1,
                'id_kota_tujuan' => 2,
                'harga' => 70000,
                'tgl_berangkat' => today()->addDays(4)->toDateString(),
                'jam_berangkat' => '10:15',
                'id_mobil' => 1,
            ])
            ->assertRedirect(route('home'));

        $this->assertDatabaseHas('jadwal', [
            'id_kota_asal' => 1,
            'id_kota_tujuan' => 2,
            'harga' => 70000,
            'jam_berangkat' => '10:15:00',
        ]);
    }

    public function test_home_page_displays_available_schedules(): void
    {
        DB::table('pesan')->insert([
            'id_pesan' => 1,
            'nomor_kursi' => '3',
            'status' => 'Lunas',
            'id_jadwal' => 1,
            'id_member' => 1,
        ]);

        $this->withSession(['legacy_account' => ['id' => 1, 'role' => 'member']])
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Bandar Lampung')
            ->assertSee('Metro')
            ->assertSee('4')
            ->assertSee('Pesan tiket');
    }
}

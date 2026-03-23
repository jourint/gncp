<?php

namespace Tests\Feature;

use App\Models\Color;
use App\Models\Material;
use App\Models\MaterialType;
use App\Models\ShoeType;
use App\Models\ShoeModel;
use App\Models\Order;
use App\Models\Customer;
use App\Models\Employee;
use App\Models\ShoeSole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseSchemaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Проверка базовых справочников и уникальности.
     */
    public function test_core_reference_tables_and_unique_constraints(): void
    {
        Color::create(['name' => 'Черный', 'hex' => '#000000']);

        $this->expectException(QueryException::class);
        Color::create(['name' => 'Черный', 'hex' => '#111111']);

        $type = MaterialType::create(['name' => 'Кожа']);
        $color = Color::first();

        $material = Material::create([
            'name' => 'Наппа',
            'material_type_id' => $type->id,
            'color_id' => $color->id,
            'stock_quantity' => 50.5
        ]);

        $this->assertEquals('Черный', $material->color->name);
    }

    /**
     * Тестирование техкарт.
     */
    public function test_shoe_models_and_technical_cards(): void
    {
        $shoeType = ShoeType::create(['name' => 'Кроссовки']);

        $model = ShoeModel::create([
            'name' => 'Air-1',
            'shoe_type_id' => $shoeType->id,
            'available_sizes' => [40, 41, 42],
        ]);

        $color = Color::create(['name' => 'Белый', 'hex' => '#FFFFFF']);
        $mType = MaterialType::create(['name' => 'Нитки']);
        $material = Material::create(['name' => 'Нить', 'material_type_id' => $mType->id, 'color_id' => $color->id]);
        $sole = ShoeSole::create(['name' => 'Vibram', 'color_id' => $color->id]);

        $techCardId = DB::table('shoe_tech_cards')->insertGetId([
            'shoe_model_id' => $model->id,
            'color_id' => $color->id,
            'shoe_sole_id' => $sole->id,
            'material_id' => $material->id,
            'name' => 'Test Card',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertDatabaseHas('shoe_tech_cards', ['id' => $techCardId]);
    }

    /**
     * Тестирование цепочки заказов.
     */
    public function test_order_lifecycle_and_employee_assignments(): void
    {
        $customer = Customer::create(['name' => 'Иван', 'phone' => '12345']);
        $order = Order::create(['customer_id' => $customer->id, 'started_at' => now()]);

        $s_type = ShoeType::create(['name' => 'Туфли']);
        $model = ShoeModel::create(['name' => 'T-100', 'shoe_type_id' => $s_type->id]);
        $color = Color::create(['name' => 'Синий', 'hex' => '#0000FF']);
        $sole = ShoeSole::create(['name' => 'Base', 'color_id' => $color->id]);
        $m_type = MaterialType::create(['name' => 'Кожа_2']);
        $material = Material::create(['name' => 'Замша', 'material_type_id' => $m_type->id]);

        $techCardId = DB::table('shoe_tech_cards')->insertGetId([
            'shoe_model_id' => $model->id,
            'color_id' => $color->id,
            'shoe_sole_id' => $sole->id,
            'material_id' => $material->id,
            'name' => 'Order Tech Card',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $position = $order->positions()->create([
            'shoe_tech_card_id' => $techCardId,
            'size_id' => 42,
            'quantity' => 10
        ]);

        $employee = Employee::create(['name' => 'Мастер', 'phone' => '98765', 'job_position_id' => 1]);

        DB::table('order_employees')->insert([
            'order_id' => $order->id,
            'order_position_id' => $position->id,
            'employee_id' => $employee->id,
            'quantity' => 10,
            'price_per_pair' => 150.00,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->assertDatabaseHas('order_employees', ['employee_id' => $employee->id]);
    }

    /**
     * Тестирование полиморфных связей мессенджера (Исправлено сравнение Enum).
     */
    public function test_polymorphic_messenger_accounts(): void
    {
        $employee = Employee::create(['name' => 'Тест', 'phone' => '111', 'job_position_id' => 1]);

        $account = $employee->messengerAccounts()->create([
            'driver' => 'telegram',
            'user_id' => 'tg-123',
            'chat_id' => 'chat-456',
        ]);

        // Если поле driver кастуется в Enum, берем ->value, если это строка — сравниваем напрямую
        $driverValue = is_object($account->driver) ? $account->driver->value : $account->driver;

        $this->assertEquals('telegram', $driverValue);
        $this->assertInstanceOf(Employee::class, $account->messengerable);
    }

    /**
     * Тестирование защиты от удаления.
     */
    public function test_enforces_restrict_on_delete_constraints(): void
    {
        $color = Color::create(['name' => 'Красный', 'hex' => '#FF0000']);
        $type = MaterialType::create(['name' => 'Ткань']);
        Material::create(['name' => 'Шелк', 'color_id' => $color->id, 'material_type_id' => $type->id]);

        $this->expectException(QueryException::class);
        $color->delete();
    }
}

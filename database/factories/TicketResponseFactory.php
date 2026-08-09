<?php

namespace Database\Factories;

use App\Models\Ticket;
use App\Models\TicketResponse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TicketResponse>
 */
class TicketResponseFactory extends Factory
{
    protected $model = TicketResponse::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ticket_id' => Ticket::factory(),
            'response' => fake()->paragraph(),
            'attachment' => '',
            'responser_name' => fake()->name(),
            'responser_id' => null,
        ];
    }
}

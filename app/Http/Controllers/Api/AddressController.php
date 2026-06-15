<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        return $request->user()->addresses()->latest()->get()->map(fn ($a) => $this->payload($a));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label' => 'required|string|max:50',
            'line1' => 'required|string|max:255',
            'line2' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'isDefault' => 'boolean',
        ]);

        $address = $request->user()->addresses()->create([
            'label' => $data['label'],
            'line1' => $data['line1'],
            'line2' => $data['line2'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'is_default' => $data['isDefault'] ?? false,
        ]);

        // First address is the default automatically; an explicit default
        // demotes the others.
        if ($address->is_default || $request->user()->addresses()->count() === 1) {
            $this->makeDefault($request, $address);
        }

        return response()->json($this->payload($address->fresh()), 201);
    }

    public function update(Request $request, Address $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);

        $data = $request->validate([
            'label' => 'sometimes|required|string|max:50',
            'line1' => 'sometimes|required|string|max:255',
            'line2' => 'nullable|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'isDefault' => 'boolean',
        ]);

        $address->update(array_filter([
            'label' => $data['label'] ?? null,
            'line1' => $data['line1'] ?? null,
            'line2' => $data['line2'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
        ], fn ($v) => $v !== null));

        if (! empty($data['isDefault'])) {
            $this->makeDefault($request, $address);
        }

        return response()->json($this->payload($address->fresh()));
    }

    public function destroy(Request $request, Address $address)
    {
        abort_unless($address->user_id === $request->user()->id, 403);
        $wasDefault = $address->is_default;
        $address->delete();

        // Promote the most recent remaining address to default if needed.
        if ($wasDefault) {
            $next = $request->user()->addresses()->latest()->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        return response()->json(['deleted' => true]);
    }

    private function makeDefault(Request $request, Address $address): void
    {
        $request->user()->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        $address->update(['is_default' => true]);
    }

    private function payload($a): array
    {
        return [
            'id' => (string) $a->id,
            'label' => $a->label,
            'line1' => $a->line1,
            'line2' => $a->line2,
            'isDefault' => (bool) $a->is_default,
            'latitude' => $a->latitude,
            'longitude' => $a->longitude,
        ];
    }
}

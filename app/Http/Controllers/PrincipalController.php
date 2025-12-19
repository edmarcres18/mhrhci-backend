<?php

namespace App\Http\Controllers;

use App\Models\Principal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PrincipalController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = (string) $request->query('search', '');
        $perPage = (int) $request->query('perPage', 10);
        $allowed = [10, 25, 50, 100];
        if (! in_array($perPage, $allowed, true)) {
            $perPage = 10;
        }

        $query = Principal::query();

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        $principals = $query
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString()
            ->through(function (Principal $principal) {
                return [
                    'id' => $principal->id,
                    'name' => $principal->name,
                    'description' => $principal->description,
                    'logo' => $principal->logo ? Storage::url($principal->logo) : null,
                    'created_at' => optional($principal->created_at)->toDateTimeString(),
                ];
            });

        return Inertia::render('Principals/Index', [
            'principals' => $principals,
            'filters' => [
                'search' => $search,
                'perPage' => $perPage,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Principals/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:2048'],
        ]);

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ];

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('principals', 'public');
        }

        Principal::create($data);

        return redirect()
            ->route('principals.index')
            ->with('success', 'Principal created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Principal $principal): Response
    {
        return Inertia::render('Principals/Edit', [
            'principal' => [
                'id' => $principal->id,
                'name' => $principal->name,
                'description' => $principal->description,
                'logo' => $principal->logo,
                'logo_url' => $principal->logo ? Storage::url($principal->logo) : null,
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Principal $principal): Response
    {
        return Inertia::render('Principals/Show', [
            'principal' => [
                'id' => $principal->id,
                'name' => $principal->name,
                'description' => $principal->description,
                'logo' => $principal->logo,
                'logo_url' => $principal->logo ? Storage::url($principal->logo) : null,
                'created_at' => optional($principal->created_at)->toDateTimeString(),
                'updated_at' => optional($principal->updated_at)->toDateTimeString(),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Principal $principal)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
        ]);

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
        ];

        $removeLogo = $request->boolean('remove_logo', false);

        if ($removeLogo && $principal->logo) {
            Storage::disk('public')->delete($principal->logo);
            $data['logo'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($principal->logo) {
                Storage::disk('public')->delete($principal->logo);
            }
            $data['logo'] = $request->file('logo')->store('principals', 'public');
        }

        $principal->update($data);

        return redirect()
            ->route('principals.index')
            ->with('success', 'Principal updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Principal $principal)
    {
        if ($principal->logo) {
            Storage::disk('public')->delete($principal->logo);
        }

        $principal->delete();

        return redirect()
            ->route('principals.index')
            ->with('success', 'Principal deleted successfully.');
    }
}

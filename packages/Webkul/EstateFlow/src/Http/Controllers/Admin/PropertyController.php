<?php

namespace Webkul\EstateFlow\Http\Controllers\Admin;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Webkul\Admin\Http\Controllers\Controller;
use Webkul\EstateFlow\Http\Requests\PropertyRequest;
use Webkul\EstateFlow\Models\Property;

class PropertyController extends Controller
{
    public function index(): View
    {
        return view('estateflow::admin.properties.index', [
            'properties' => Property::query()->latest('id')->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('estateflow::admin.properties.create', [
            'property' => new Property,
        ]);
    }

    public function store(PropertyRequest $request): RedirectResponse
    {
        $property = Property::query()->create($request->validated());

        session()->flash('success', trans('estateflow::app.properties.messages.created'));

        return redirect()->route('admin.estateflow.properties.show', $property->id);
    }

    public function show(int $id): View
    {
        return view('estateflow::admin.properties.show', [
            'property' => $this->findProperty($id),
        ]);
    }

    public function edit(int $id): View
    {
        return view('estateflow::admin.properties.edit', [
            'property' => $this->findProperty($id),
        ]);
    }

    public function update(PropertyRequest $request, int $id): RedirectResponse
    {
        $property = $this->findProperty($id);
        $property->update($request->validated());

        session()->flash('success', trans('estateflow::app.properties.messages.updated'));

        return redirect()->route('admin.estateflow.properties.show', $property->id);
    }

    public function toggleStatus(int $id): RedirectResponse
    {
        $property = $this->findProperty($id);
        $property->update(['active' => ! $property->active]);

        session()->flash('success', trans('estateflow::app.properties.messages.status-updated'));

        return redirect()->route('admin.estateflow.properties.show', $property->id);
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->findProperty($id)->delete();

        session()->flash('success', trans('estateflow::app.properties.messages.deleted'));

        return redirect()->route('admin.estateflow.properties.index');
    }

    private function findProperty(int $id): Property
    {
        return Property::query()->findOrFail($id);
    }
}

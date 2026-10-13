@extends('layouts.app')

@section('content')
    <x-formula.editor
        title="Formula FG"
        description="Kelola komposisi raw material berdasarkan code FG."
        lookup-label="Code FG"
        lookup-placeholder="Pilih Finished Good"
        lookup-search-placeholder="Cari kode atau deskripsi Finished Good..."
        ingredient-code-label="Code RM"
        ingredient-description-label="Description RM"
        list-id="formula-fg-master-list"
        ingredient-list-id="formula-fg-ingredient-list"
        :master-items="$masterItems"
        :ingredient-items="$ingredientItems"
        :initial-formulas="[]"
        :show-batch="true"
        standard-batch-label="Standard Batch"
        standard-label="Standard Unit"
        standard-step="0.0000000001"
        :calculate-standard="true"
        :split-packaging="true"
        :show-notes="true"
        export-url="{{ route('admin.maintenance.formula.fg.export') }}"
        import-url-template="{{ route('admin.maintenance.formula.fg.import', ['finishedGood' => '__MASTER__']) }}"
        raw-material-create-url="{{ route('admin.maintenance.raw-material.create') }}"
        lookup-url="{{ route('admin.maintenance.formula.fg.data') }}"
        save-url-template="{{ route('admin.maintenance.formula.fg.update', ['finishedGood' => '__MASTER__']) }}" />
@endsection

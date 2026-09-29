@extends('layouts.app')

@section('content')
    <x-formula.editor
        title="Formula NDL"
        description="Kelola komposisi finished good berdasarkan noodle code."
        lookup-label="Noodle Code"
        lookup-placeholder="Masukkan noodle code lalu tekan Enter..."
        ingredient-code-label="Code FG"
        ingredient-description-label="Description FG"
        list-id="formula-ndl-master-list"
        ingredient-list-id="formula-ndl-ingredient-list"
        :master-items="$masterItems"
        :ingredient-items="$ingredientItems"
        :initial-formulas="$initialFormulas" />
@endsection

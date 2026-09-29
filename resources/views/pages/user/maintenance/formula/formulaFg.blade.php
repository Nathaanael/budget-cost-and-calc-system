@extends('layouts.app')

@section('content')
    <x-formula.editor
        title="Formula FG"
        description="Kelola komposisi raw material berdasarkan code FG."
        lookup-label="Code FG"
        lookup-placeholder="Masukkan code FG lalu tekan Enter..."
        ingredient-code-label="Code RM"
        ingredient-description-label="Description RM"
        list-id="formula-fg-master-list"
        ingredient-list-id="formula-fg-ingredient-list"
        :master-items="$masterItems"
        :ingredient-items="$ingredientItems"
        :initial-formulas="$initialFormulas" />
@endsection

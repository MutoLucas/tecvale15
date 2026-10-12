@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Paginação" class="row" style="align-items:center;">
        @if ($paginator->onFirstPage())
            <span class="btn btn-sm muted" aria-disabled="true">&laquo; Anterior</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-sm" rel="prev">&laquo; Anterior</a>
        @endif

        <span class="muted">Página {{ $paginator->currentPage() }} de {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-sm" rel="next">Próxima &raquo;</a>
        @else
            <span class="btn btn-sm muted" aria-disabled="true">Próxima &raquo;</span>
        @endif
    </nav>
@endif

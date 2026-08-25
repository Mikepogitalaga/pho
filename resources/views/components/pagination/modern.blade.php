@props([
    'paginator',
    'showAllOption' => true,
    'defaultPerPage' => 15,
    'perPageOptions' => [15, 30, 50, 100],
])

@php
    $perPage = request('per_page', $defaultPerPage);
    $total = $paginator->total();
    $first = $paginator->firstItem();
    $last = $paginator->lastItem();
    $currentPage = $paginator->currentPage();
    $lastPage = $paginator->lastPage();
    $uniqueId = 'pagination-' . uniqid();
@endphp

<div class="pagination-modern-wrapper">
    <div class="pagination-modern-inner">
        <div class="pagination-left">
            <div class="entries-info">
                <span class="entries-badge">
                    <i class="fas fa-list-ol"></i>
                    {{ $first }}-{{ $last }} of {{ number_format($total) }}
                </span>
            </div>
            <div class="per-page-selector">
                <span class="selector-label">Show</span>
                <div class="selector-select">
                    <select name="per_page" id="{{ $uniqueId }}-per-page" onchange="changePerPage(this.value)">
                        @foreach($perPageOptions as $option)
                            <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>{{ $option }}</option>
                        @endforeach
                        @if($showAllOption)
                            <option value="all" {{ $perPage === 'all' ? 'selected' : '' }}>All</option>
                        @endif
                    </select>
                    <span class="selector-suffix">/ page</span>
                </div>
            </div>
        </div>

        @if($paginator->hasPages())
            <nav aria-label="Pagination Navigation">
                <ul class="pagination-modern">
                    {{-- First Page Link --}}
                    @if ($currentPage > 3)
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->url(1) }}" title="First Page" aria-label="First page">
                                <i class="fas fa-angle-double-left"></i>
                            </a>
                        </li>
                    @endif

                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link" aria-hidden="true"><i class="fas fa-chevron-left"></i></span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" title="Previous" aria-label="Previous page">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($paginator->getUrlRange(max(1, $currentPage - 2), min($lastPage, $currentPage + 2)) as $page => $url)
                        @if ($page == $currentPage)
                            <li class="page-item active" aria-current="page">
                                <span class="page-link current-page">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $url }}" aria-label="Page {{ $page }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" title="Next" aria-label="Next page">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                    @else
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link" aria-hidden="true"><i class="fas fa-chevron-right"></i></span>
                        </li>
                    @endif

                    {{-- Last Page Link --}}
                    @if ($currentPage < $lastPage - 2)
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->url($lastPage) }}" title="Last Page" aria-label="Last page">
                                <i class="fas fa-angle-double-right"></i>
                            </a>
                        </li>
                    @endif
                </ul>
            </nav>
        @endif

        {{-- Quick Jump --}}
        @if ($lastPage > 10)
            <div class="quick-jump">
                <span class="quick-jump-label">Jump to</span>
                <div class="quick-jump-input">
                    <input type="number" class="form-control form-control-sm" 
                           id="{{ $uniqueId }}-jump-to-page" min="1" max="{{ $lastPage }}" 
                           placeholder="{{ $currentPage }}">
                    <button class="btn btn-sm btn-primary" type="button" onclick="jumpToPage('{{ $uniqueId }}')" aria-label="Go to page">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

@push('styles')
@once
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
/* ===== Modern Pagination Component ===== */
.pagination-modern-wrapper {
    margin-top: 1.25rem;
    background: linear-gradient(135deg, rgba(255,255,255,0.96) 0%, rgba(248,250,252,0.96) 100%);
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 16px;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -2px rgba(0,0,0,0.02);
    backdrop-filter: blur(8px);
}

.dark-mode .pagination-modern-wrapper {
    background: linear-gradient(135deg, rgba(30,41,59,0.96) 0%, rgba(51,65,85,0.96) 100%);
    border-color: rgba(148, 163, 184, 0.12);
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.2), 0 2px 4px -2px rgba(0,0,0,0.15);
}

.pagination-modern-inner {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 0.85rem 1.25rem;
}

.pagination-left {
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.entries-info .entries-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.9rem;
    border-radius: 999px;
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: 0.01em;
    color: #ffffff;
    background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
    box-shadow: 0 2px 8px rgba(37, 99, 235, 0.25), 0 0 0 1px rgba(255,255,255,0.15) inset;
    white-space: nowrap;
}

.entries-info .entries-badge i {
    font-size: 0.75rem;
    opacity: 0.9;
}

.per-page-selector {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: var(--text-muted);
    font-weight: 500;
}

.selector-select {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
}

.selector-select select {
    appearance: none;
    padding: 0.45rem 2rem 0.45rem 0.75rem;
    border-radius: 10px;
    border: 1px solid rgba(15, 23, 42, 0.08);
    background: #ffffff;
    color: var(--text);
    font-size: 0.8rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
}

.selector-select select:hover {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.08);
}

.selector-select select:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
}

.selector-select::after {
    content: '\f107';
    font-family: 'Font Awesome 6 Free';
    font-weight: 900;
    position: absolute;
    right: 2.5rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.65rem;
    color: var(--text-muted);
    pointer-events: none;
}

.dark-mode .selector-select select {
    background: rgba(30, 41, 59, 0.8);
    border-color: rgba(148, 163, 184, 0.15);
    color: var(--text);
}

.dark-mode .selector-select select:hover {
    border-color: var(--primary);
}

.selector-suffix {
    font-size: 0.78rem;
    color: var(--text-muted);
    font-weight: 500;
}

/* Pagination Navigation */
.pagination-modern {
    display: flex;
    align-items: center;
    gap: 0.35rem;
    list-style: none;
    margin: 0;
    padding: 0;
    flex-wrap: wrap;
}

.pagination-modern .page-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 2.4rem;
    height: 2.4rem;
    padding: 0 0.65rem;
    border-radius: 10px;
    border: 1px solid rgba(15, 23, 42, 0.06);
    background: #ffffff;
    color: var(--text);
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    box-shadow: 0 1px 2px rgba(0,0,0,0.02);
}

.pagination-modern .page-link i {
    font-size: 0.75rem;
    transition: transform 0.2s ease;
}

.pagination-modern .page-item:not(.active):not(.disabled) .page-link:hover {
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.08) 0%, rgba(124, 58, 237, 0.08) 100%);
    border-color: rgba(37, 99, 235, 0.2);
    color: var(--primary);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.12);
}

.pagination-modern .page-item:not(.active):not(.disabled) .page-link:hover i {
    transform: scale(1.1);
}

.pagination-modern .page-item.active .page-link {
    background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
    border-color: transparent;
    color: #ffffff;
    font-weight: 700;
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35), 0 0 0 1px rgba(255,255,255,0.2) inset;
    transform: translateY(-1px);
}

.pagination-modern .page-item.disabled .page-link {
    color: #94a3b8;
    background: rgba(248, 250, 252, 0.6);
    border-color: rgba(15, 23, 42, 0.04);
    cursor: not-allowed;
    opacity: 0.6;
}

.dark-mode .pagination-modern .page-link {
    background: rgba(30, 41, 59, 0.7);
    border-color: rgba(148, 163, 184, 0.1);
    color: var(--text);
    box-shadow: 0 1px 2px rgba(0,0,0,0.15);
}

.dark-mode .pagination-modern .page-item:not(.active):not(.disabled) .page-link:hover {
    background: linear-gradient(135deg, rgba(96, 165, 250, 0.15) 0%, rgba(139, 92, 246, 0.15) 100%);
    border-color: rgba(96, 165, 250, 0.3);
    box-shadow: 0 4px 12px rgba(96, 165, 250, 0.15);
}

.dark-mode .pagination-modern .page-item.disabled .page-link {
    background: rgba(30, 41, 59, 0.5);
    color: #64748b;
}

/* Quick Jump */
.quick-jump {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 0.85rem;
    border-radius: 10px;
    background: rgba(37, 99, 235, 0.04);
    border: 1px solid rgba(37, 99, 235, 0.08);
    font-size: 0.8rem;
    color: var(--text-muted);
    font-weight: 500;
}

.quick-jump-label {
    white-space: nowrap;
}

.quick-jump-input {
    display: flex;
    align-items: center;
    gap: 0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    border-radius: 8px;
    overflow: hidden;
}

.quick-jump-input .form-control {
    border: 1px solid rgba(15, 23, 42, 0.08);
    border-radius: 8px 0 0 8px;
    border-right: none;
    padding: 0.35rem 0.5rem;
    font-size: 0.8rem;
    font-weight: 500;
    text-align: center;
    width: 64px;
    background: #ffffff;
    color: var(--text);
    transition: all 0.2s ease;
}

.quick-jump-input .form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.1);
}

.quick-jump-input .btn {
    border-radius: 0 8px 8px 0;
    padding: 0.35rem 0.6rem;
    font-size: 0.75rem;
    border: 1px solid rgba(37, 99, 235, 0.2);
    background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
    color: white;
    box-shadow: 0 2px 6px rgba(37, 99, 235, 0.2);
}

.quick-jump-input .btn:hover {
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
    transform: translateY(-1px);
}

.dark-mode .quick-jump {
    background: rgba(96, 165, 250, 0.08);
    border-color: rgba(96, 165, 250, 0.12);
}

.dark-mode .quick-jump-input .form-control {
    background: rgba(30, 41, 59, 0.8);
    border-color: rgba(148, 163, 184, 0.15);
    color: var(--text);
}

.dark-mode .quick-jump-input .btn {
    border-color: rgba(96, 165, 250, 0.25);
}

/* Responsive */
@media (max-width: 768px) {
    .pagination-modern-inner {
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
        padding: 1rem;
    }

    .pagination-left {
        justify-content: space-between;
    }

    .pagination-modern {
        justify-content: center;
        gap: 0.25rem;
    }

    .pagination-modern .page-link {
        min-width: 2.1rem;
        height: 2.1rem;
        padding: 0 0.5rem;
        font-size: 0.8rem;
        border-radius: 8px;
    }

    .quick-jump {
        justify-content: center;
    }

    .entries-info .entries-badge {
        font-size: 0.75rem;
        padding: 0.4rem 0.75rem;
    }
}

@media (max-width: 480px) {
    .per-page-selector {
        width: 100%;
        justify-content: space-between;
    }

    .selector-select select {
        flex: 1;
    }
}
</style>
@endonce
@endpush

@push('scripts')
@once
<script>
function changePerPage(perPage) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

function jumpToPage(uniqueId) {
    const pageInput = document.getElementById(uniqueId + '-jump-to-page');
    const pageNumber = parseInt(pageInput.value);
    const maxPage = parseInt(pageInput.getAttribute('max'));
    
    if (pageNumber && pageNumber >= 1 && pageNumber <= maxPage) {
        const url = new URL(window.location.href);
        url.searchParams.set('page', pageNumber);
        window.location.href = url.toString();
    } else {
        pageInput.classList.add('is-invalid');
        setTimeout(() => pageInput.classList.remove('is-invalid'), 2000);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.quick-jump-input input').forEach(function(input) {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const uniqueId = this.id.replace('-jump-to-page', '');
                jumpToPage(uniqueId);
            }
        });
    });
});
</script>
@endonce
@endpush

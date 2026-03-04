{{-- resources/views/components/alerts.blade.php --}}

@if (session('success'))
    @push('scripts')
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: '{{ session('success') }}',
            timer: 3000,
            showConfirmButton: false,
            toast: true,
            position: 'top-end',
        });
    </script>
    @endpush
@endif

@if (session('error'))
    @push('scripts')
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('error') }}',
        });
    </script>
    @endpush
@endif

@if ($errors->any())
    @push('scripts')
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            html: `{!! implode('<br>', $errors->all()) !!}`,
        });
    </script>
    @endpush
@endif
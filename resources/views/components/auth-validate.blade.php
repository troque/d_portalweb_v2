<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100"
    style="background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('/assets/images/codigo-acceso.jpg');
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center center;
        height: 100vh;
        width: 100%;">

    <div class="w-full mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg" style="width: 65%;">

        <div style="text-align: -webkit-center;" class="mb-4">
            {{ $logo }}
        </div>

        {{ $slot }}
    </div>
</div>

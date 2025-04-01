<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/tailwindcss@2.2.19/dist/tailwind.min.css" rel=" stylesheet">
	<!--Replace with your tailwind.css once created-->

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4F46E5',
                        secondary: '#6B7280'
                    }
                }
            }
        };
</script>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white h-full p-5 shadow-md flex flex-col">
            <h1 class="text-2xl font-bold font-serif text-amber-900 flex items-center space-x-2">
                <img src="images.png" alt="Anoor Cloud Logo" class="w-24 h-12 pl-8">
            </h1>

            <nav class="mt-5">
                <ul class="space-y-2">
                    <li class="p-3 hover:bg-gray-200 rounded-lg cursor-pointer flex items-center">
                        <a href="{{ route('dashboard') }}" class="flex items-center space-x-2">
                            <!-- Dashboard Icon (Home Icon) -->
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 9L12 2L21 9"></path>
                                <path d="M9 22V12H15V22"></path>
                            </svg>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <!-- Products -->
    <li class="p-3 hover:bg-gray-200 rounded-lg cursor-pointer flex items-center">
        <a href="{{ route('products') }}" class="flex items-center space-x-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 3h18v18H3V3z"></path>
                <path d="M3 9h18"></path>
            </svg>
            <span>Products</span>
        </a>
    </li>

    <!-- Links -->
    <li class="p-3 hover:bg-gray-200 rounded-lg cursor-pointer flex items-center">
        <a href="{{ route('links') }}" class="flex items-center space-x-2">
            <svg class="w-[27px] h-[27px] text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M13.213 9.787a3.391 3.391 0 0 0-4.795 0l-3.425 3.426a3.39 3.39 0 0 0 4.795 4.794l.321-.304m-.321-4.49a3.39 3.39 0 0 0 4.795 0l3.424-3.426a3.39 3.39 0 0 0-4.794-4.795l-1.028.961"/>
              </svg>


            <span>Links</span>
        </a>
    </li>

    <!-- Source -->
    <li class="p-3 hover:bg-gray-200 rounded-lg cursor-pointer flex items-center">
        <a href="{{ route('source.create') }}" class="flex items-center space-x-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 18L22 12 16 6"></path>
                <path d="M8 6L2 12l6 6"></path>
                <path d="M10 12H2"></path>
                <path d="M14 12h8"></path>
            </svg>
            <span>Source</span>
        </a>
    </li>
      </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col p-6">
            <div class="flex justify-between items-center mb-6">
                <h2 id="page-title" class="text-3xl font-bold">Source</h2>
                     <!--  <input type="text" placeholder="Search" class="px-4 py-2 border rounded-lg w-64">-->
            </div>

<!--Container-->
<form action="{{ route('source.store') }}" method="POST">
    @csrf

    <div class="space-y-12">


      <div class="border-b border-gray-900/10 pb-12">

        <div class="mt-10 grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
          <div class="sm:col-span-3">
            <label for="name" class="block text-sm/6 font-medium text-gray-900">Name</label>
            <div class="mt-2">
              <input type="text" name="name" id="name" autocomplete="off" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
            </div>
          </div>

          <br>
          <div class="sm:col-span-3">
            <label for="desc" class="block text-sm/6 font-medium text-gray-900">Desc</label>
            <div class="mt-2">
              <input type="text" name="desc" id="desc" autocomplete="off" class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
            </div>
          </div>

<br>



          <div class="sm:col-span-3">
            <label for="status" class="block text-sm/6 font-medium text-gray-900">status</label>
            <div class="mt-2 grid grid-cols-1">
              <select id="status" name="status" autocomplete="off" class="col-start-1 row-start-1 w-full appearance-none rounded-md bg-white py-1.5 pr-8 pl-3 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6">
                <option>0</option>
                <option>1</option>

              </select>
              <svg class="pointer-events-none col-start-1 row-start-1 mr-2 size-5 self-center justify-self-end text-gray-500 sm:size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true" data-slot="icon">
                <path fill-rule="evenodd" d="M4.22 6.22a.75.75 0 0 1 1.06 0L8 8.94l2.72-2.72a.75.75 0 1 1 1.06 1.06l-3.25 3.25a.75.75 0 0 1-1.06 0L4.22 7.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>

<br>






    <div class="mt-6 flex items-center justify-end gap-x-6">
        <a href="{{ route('addsource') }}" class="px-4 py-2 bg-white-500 text-black text-sm font-medium rounded-lg shadow hover:bg-orange-300">cancel</a>
        <button type="submit" class="rounded-md bg-orange-500 px-3 py-2 text-sm font-semibold text-white shadow-xs hover:bg-orange-300 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-black-300">Save</button>
    </div>
  </form>

</body>

</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://unpkg.com/tailwindcss@2.2.19/dist/tailwind.min.css" rel=" stylesheet">
	<!--Replace with your tailwind.css once created-->


	<!--Regular Datatables CSS-->
	<link href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.min.css" rel="stylesheet">
	<!--Responsive Extension Datatables CSS-->
	<link href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.dataTables.min.css" rel="stylesheet">

	<style>
		/*Overrides for Tailwind CSS */

		/*Form fields*/
		.dataTables_wrapper select,
		.dataTables_wrapper .dataTables_filter input {
			color: #4a5568;
			/*text-gray-700*/
			padding-left: 1rem;
			/*pl-4*/
			padding-right: 1rem;
			/*pl-4*/
			padding-top: .5rem;
			/*pl-2*/
			padding-bottom: .5rem;
			/*pl-2*/
			line-height: 1.25;
			/*leading-tight*/
			border-width: 2px;
			/*border-2*/
			border-radius: .25rem;
			border-color: #edf2f7;
			/*border-gray-200*/
			background-color: #edf2f7;
			/*bg-gray-200*/
		}

		/*Row Hover*/
		table.dataTable.hover tbody tr:hover,
		table.dataTable.display tbody tr:hover {
			background-color: #ebf4ff;
			/*bg-indigo-100*/
		}

		/*Pagination Buttons*/
		.dataTables_wrapper .dataTables_paginate .paginate_button {
			font-weight: 700;
			/*font-bold*/
			border-radius: .25rem;
			/*rounded*/
			border: 1px solid transparent;
			/*border border-transparent*/
		}

		/*Pagination Buttons - Current selected */
		.dataTables_wrapper .dataTables_paginate .paginate_button.current {
			color: #fff !important;
			/*text-white*/
			box-shadow: 0 1px 3px 0 rgba(0, 0, 0, .1), 0 1px 2px 0 rgba(0, 0, 0, .06);
			/*shadow*/
			font-weight: 700;
			/*font-bold*/
			border-radius: .25rem;
			/*rounded*/
			background: #667eea !important;
			/*bg-indigo-500*/
			border: 1px solid transparent;
			/*border border-transparent*/
		}

		/*Pagination Buttons - Hover */
		.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
			color: #fff !important;
			/*text-white*/
			box-shadow: 0 1px 3px 0 rgba(0, 0, 0, .1), 0 1px 2px 0 rgba(0, 0, 0, .06);
			/*shadow*/
			font-weight: 700;
			/*font-bold*/
			border-radius: .25rem;
			/*rounded*/
			background: #667eea !important;
			/*bg-indigo-500*/
			border: 1px solid transparent;
			/*border border-transparent*/
		}

		/*Add padding to bottom border */
		table.dataTable.no-footer {
			border-bottom: 1px solid #e2e8f0;
			/*border-b-1 border-gray-300*/
			margin-top: 0.75em;
			margin-bottom: 0.75em;
		}

		/*Change colour of responsive icon*/
		table.dataTable.dtr-inline.collapsed>tbody>tr>td:first-child:before,
		table.dataTable.dtr-inline.collapsed>tbody>tr>th:first-child:before {
			background-color: #667eea !important;
			/*bg-indigo-500*/
		}
	</style>
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
   <!-- Links -->
   <li class="p-3 hover:bg-gray-200 rounded-lg cursor-pointer flex items-center">
    <a href="{{ route('links') }}" class="flex items-center space-x-2">
        <svg class="w-[27px] h-[27px] text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M13.213 9.787a3.391 3.391 0 0 0-4.795 0l-3.425 3.426a3.39 3.39 0 0 0 4.795 4.794l.321-.304m-.321-4.49a3.39 3.39 0 0 0 4.795 0l3.424-3.426a3.39 3.39 0 0 0-4.794-4.795l-1.028.961"/>
          </svg>


        <span>Links</span>
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



    <!-- Source -->
    <li class="p-3 hover:bg-gray-200 rounded-lg cursor-pointer flex items-center">
        <a href="{{ route('source') }}" class="flex items-center space-x-2">
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

<div class="pb-4 flex justify-end pr-2 rounded shadow bg-white-900 p-4">
    <a href="{{ route('addproduct') }}" class="px-4 py-2 bg-orange-500 text-white text-sm font-medium rounded-lg shadow hover:bg-orange-300 mr-2">
        Add Source
    </a>
    <a href="{{ route('sources.export') }}" class="px-4 py-2 bg-orange-500 text-white text-sm font-medium rounded-lg shadow hover:bg-orange-300">
        Excel Download
    </a>
</div>

    <!--Card-->
    <div id='recipients' class="p-8 mt-6 lg:mt-0 rounded shadow bg-white">


        <table id="example" class="stripe hover" style="width:100%; padding-top: 1em;  padding-bottom: 1em;">

            <thead>
                <tr>
                    <th data-priority="1">Name</th>
                    <th data-priority="2">desc</th>
                    <th data-priority="3">status</th>
                    <th data-priority="4">created_on</th>
                    <th data-priority="5">updated_on</th>

                </tr>
            </thead>
            <tbody>


                @foreach ($Source as $sources)


                <tr>
                    <td>{{ $sources->name }}</td>
                    <td>{{ $sources->desc }}</td>
                    <td>{{ $sources->status }}</td>
                    <td>{{ $sources->created_on }}</td>
                    <td>{{ $sources->updated_on }}</td>
                </tr>
            @endforeach

            </tbody>

        </table>


    </div>
    <!--/Card-->



<!--/container-->

</div>



<!-- jQuery -->
<script type="text/javascript" src="https://code.jquery.com/jquery-3.4.1.min.js"></script>

<!--Datatables -->
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script>
<script>
    $(document).ready(function () {

        var table = $('#example').DataTable({
            responsive: true
        })
            .columns.adjust()
            .responsive.recalc();
    });
</script>

</body>

</html>

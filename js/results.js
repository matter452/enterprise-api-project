let results = sessionStorage.getItem('results');
const parsedResults = JSON.parse(results);
console.log(results);
    if(results)
    {
    
        const table_body = document.getElementById("table_body");
        const dataArr = parsedResults.data;
        console.log(typeof parsedResults);
        console.log(typeof dataArr);

        dataArr.forEach((doc) => {
            const row = document.createElement('tr');
            row.setAttribute("class", "bg-white even:bg-gray-100")

            row.innerHTML = `
                
                    <td class="px-4 py-2 text-sm">${doc.doc_id}</td>
                    <td class="px-4 py-2 text-sm">${doc.doc_loan_number}</td>
                    <td class="px-4 py-2 text-sm">${doc.doc_type}</td>
                    <td id="${doc.doc_id}" class="px-4 py-2 text-sm">
                        <a class="underline underline-offset-2 text-neutral-950 hover:decoration-2 hover:text-blue-950"
                        target="_blank" href="/file.php?fid=${doc.doc_id}">
                        ${doc.file_name}
                        </a>
                    </td>
                    <td class="px-4 py-2 text-sm">${(doc.file_size / (Math.pow(1024,2))).toFixed(1)}Mb</td>
                    <td class="px-4 py-2 text-sm">${doc.last_access}</td>
                    <td class="px-4 py-2 text-sm">${doc.upload_datetime}</td>`;
                    table_body.appendChild(row);
        });

    } 
    else
    {
        document.getElementById('results_table').innerHTML = '<p>No data available.</p>';
    }

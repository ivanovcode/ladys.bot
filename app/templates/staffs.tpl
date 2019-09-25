<div class="table-responsive">
    <div id="no-more-tables">
        <table class="table">
            <thead>
            <tr>
                <th scope="col">ID Сотрудника</th>
                <th scope="col">ФИО</th>
                <th scope="col">Телефон</th>
            </tr>
            </thead>
            <tbody>
            {%*staff*}
            <tr>
                <td data-title="ID Сотрудника:">{*staff:id*}</td>
                <td data-title="ФИО:">{*staff:name*}</td>
                <td data-title="Телефон:">{*staff:phone*}</td>
            </tr>
            {%}
            </tbody>
        </table>
    </div>
</div>
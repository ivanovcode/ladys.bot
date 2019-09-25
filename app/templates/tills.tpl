<div class="table-responsive">
    <div id="no-more-tables">
        <table class="table">
            <thead>
            <tr>
                <th scope="col">ID кассы</th>
                <th scope="col">Название кассы</th>
                <th scope="col"></th>
            </tr>
            </thead>
            <tbody>
            {%*till*}
            <tr>
                <td data-title="ID кассы:" data-key="TillId">{*till:id*}</td>
                <td data-title="Название кассы:">{*till:title*}{?* till:is_new = 1 *}<span class="badge danger">Нет в боте</span>{?}</td>
                <td>
                    {?* till:is_new = 1 *}<span data-mode="add">[Добавить в бота]</span>{?}
                </td>
            </tr>
            {%}
            </tbody>
        </table>
    </div>
</div>
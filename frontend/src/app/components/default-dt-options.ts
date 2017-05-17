/**
 * Опции по умолчанию для datatables.net
 *
 * @see http://datatables.net/
 */

export const defaultDtOptions = {
    oLanguage: {
        sSearch: 'Поиск',
        oPaginate: {
            sFirst: 'Первая',
            sLast: 'Последняя',
            sNext: 'Следующая',
            sPrevious: 'Предыдущая'
        },
        sEmptyTable: 'Нет данных для отображения',
        sZeroRecords: 'Нет данных для отображения',
        sInfo: '',
        sInfoEmpty: '0 записей найдено',
        sInfoFiltered: ' - отфильтровано из _MAX_ записей',
        sInfoPostFix: '',
        sInfoThousands: ' ',
        sLoadingRecords: 'Загрузка...',
    },
    displayLength: 50,
    lengthChange: false
};

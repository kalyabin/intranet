/**
 * Ответ для списка с постраничной навигацией
 */
export interface ListInterface {
    pageSize: number;
    pageNum: number;
    totalCount: number;
}

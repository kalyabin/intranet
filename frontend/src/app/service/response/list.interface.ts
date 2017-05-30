/**
 * Ответ для списка с постраничной навигацией
 */
export interface ListInterface<T> {
    pageSize: number;
    pageNum: number;
    totalCount: number;
    list: T[];
}

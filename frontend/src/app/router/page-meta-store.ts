import Vuex from "vuex";

/**
 * Конфигурация meta-данных
 */
export interface PageMetaStateInterface {
    pageTitle: string;
    title: string;
    pageLoader: boolean;
}

/**
 * Состояние мета-данных страницы
 */
export const pageMetaStore = new Vuex.Store({
   state:  <PageMetaStateInterface>{
       pageTitle: '',
       title: '',
       pageLoader: false,
   },
    mutations: {
        setPageTitle: (state: PageMetaStateInterface, pageTitle: string) => {
           state.pageTitle = pageTitle;
        },
        setTitle: (state: PageMetaStateInterface, title: string) => {
            state.title = title;
        },
        showPageLoader: (state: PageMetaStateInterface) => {
            state.pageLoader = true;
        },
        hidePageLoader: (state: PageMetaStateInterface) => {
            state.pageLoader = false;
        },
    }
});

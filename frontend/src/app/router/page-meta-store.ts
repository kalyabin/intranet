import Vuex from "vuex";

/**
 * Конфигурация meta-данных
 */
export interface PageMetaStateInterface {
    pageTitle: string;
    title: string;
}

/**
 * Состояние мета-данных страницы
 */
export const pageMetaStore = new Vuex.Store({
   state:  <PageMetaStateInterface>{
       pageTitle: '',
       title: ''
   },
    mutations: {
        setPageTitle: (state: PageMetaStateInterface, pageTitle: string) => {
           state.pageTitle = pageTitle;
        },
        setTitle: (state: PageMetaStateInterface, title: string) => {
            state.title = title;
        }
    }
});

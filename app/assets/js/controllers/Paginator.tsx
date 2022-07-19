import React from 'react';
import {Col, Form, Pagination, Row} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';

interface PaginationProps {
    query: any,
    currentIndex: number,
    setCurrentIndex: (value: number) => void,
    currentMaxResults: number,
    setCurrentMaxResults: (value: number) => void,
}

const Paginator: React.VFC<PaginationProps> = (
    {
        query,
        currentIndex,
        setCurrentIndex,
        currentMaxResults,
        setCurrentMaxResults
    }) => {

    const {t} = useTranslation();
    const totalCount = query.data.count;
    const totalPages = Math.max(Math.ceil(totalCount / currentMaxResults), 1);
    const lastPageIndex = (totalPages - 1) * currentMaxResults;

    return (
        <Row>
            <Col>
                <Pagination size="lg">
                    <Pagination.First onClick={() => setCurrentIndex(0)}/>
                    <Pagination.Prev
                        onClick={() => setCurrentIndex(Math.max(currentIndex - currentMaxResults, 0))}/>
                    <Pagination.Next
                        onClick={() => setCurrentIndex(Math.min(currentIndex + currentMaxResults, lastPageIndex))}/>
                    <Pagination.Last onClick={() => setCurrentIndex(lastPageIndex)}/>
                </Pagination>
            </Col>
            <Col lg={2}>
                <Row>
                    <Form.Label column="sm">
                        {t('paging.itemsPerPage')}
                    </Form.Label>
                    <Col>
                        <Form.Select
                            aria-label={t('paging.itemsPerPage')}
                            value={currentMaxResults}
                            onChange={(e) => {
                                setCurrentMaxResults(parseInt(e.target.value));
                            }}>
                            <option value="1">1</option>
                            <option value="5">5</option>
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                        </Form.Select>
                    </Col>
                </Row>
            </Col>
        </Row>
    );
};

Paginator.defaultProps = {};

export default Paginator;

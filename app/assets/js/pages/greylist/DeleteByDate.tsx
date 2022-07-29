import {format} from 'date-fns';
import React, {useState} from 'react';
import {Col, Form} from 'react-bootstrap';
import {useTranslation} from 'react-i18next';
import {useMutation} from 'react-query';
import DatePicker from 'react-datepicker';

import DeleteButton from '../../controllers/Buttons/DeleteButton';
import ModalConfirmation from '../../controllers/ModalConfirmation';
import {DATE_FORMAT} from '../../types/common';

interface DeleteByDateProps {
    onDelete: () => void
}

const DeleteByDate = ({onDelete}: DeleteByDateProps) => {
    const {t} = useTranslation();
    const [date, setDate] = useState(new Date());
    const [show, setShow] = useState(false);

    const deleteRecord = useMutation(
        (values: { date: string }) => fetch('/api/greylist/delete-to-date', {
            method: 'DELETE',
            body: JSON.stringify(values)
        }).then(async response => {
            const data = await response.json();

            // check for error response
            if (!response.ok) {
                // get error message from body or default to response status
                const error = (data && data.message) || response.status;
                return Promise.reject(error);
            }
            setShow(false);
            onDelete();
        }).catch(error => {
            console.error('There was an error!', error);
        }));

    return (
        <>
            <DeleteButton onClick={() => setShow(true)} label="button.deleteByDate"/>

            <ModalConfirmation
                show={show}
                onConfirm={() => deleteRecord.mutateAsync({date: format(date, DATE_FORMAT)})}
                onCancel={() => setShow(false)}
                title="greylist.deleteByDateHeader">

                {t('greylist.deleteByDateMessage')}

                <Form.Group as={Col} md="12"
                            className="mt-2">
                    <DatePicker selected={date}
                                onChange={(date: Date) => setDate(date)}
                                className="form-control"
                                dateFormat={DATE_FORMAT}/>
                </Form.Group>

            </ModalConfirmation>
        </>
    );
};

export default DeleteByDate;
